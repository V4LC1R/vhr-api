<?php

namespace Modules\Attendance\Services;

use DB;
use App\Contracts\DailyEngagementRepositoryInterface;
use App\Contracts\TimeEntryRepositoryInterface;
use App\Exceptions\DomainException;
use Illuminate\Support\Carbon;
use Modules\Attendance\Data\TimeEntryData;
use Modules\Attendance\Enums\DailyEngagementStatusEnum;
use Modules\Attendance\Enums\TimeEntrySourceEnum;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Attendance\Models\TimeEntry;
use Modules\Attendance\Support\AttendanceCalculator;
use Modules\Attendance\Support\DayResolver;
use Spatie\LaravelData\Optional;

class TimeEntryService
{
    public function __construct(
        protected TimeEntryRepositoryInterface $timeEntryRepository,
        protected DailyEngagementRepositoryInterface $dailyEngagementRepository,
        protected AttendanceCalculator $calculator,
        protected DayResolver $dayResolver,
    ) {
    }

    public function create(TimeEntryData $data)
    {
        $company   = $this->resolveCompany();
        $employee  = $this->dayResolver->findEmployee($data->employeeId, $company->companyId);
        $punchedAt = $this->normalizePunchedAt($data->punchedAt);
        $date      = substr($punchedAt, 0, 10);

        $this->ensureNoConflictingPunch($employee->id, $punchedAt);

        return DB::transaction(function () use ($data, $company, $employee, $date, $punchedAt) {
            $day = $this->dayResolver->resolve($company->companyId, $employee, $date);

            $timeEntry = $this->timeEntryRepository->create([
                'companyId'         => $company->companyId,
                'dailyEngagementId' => $day->id,
                'punchedAt'        => $punchedAt,
                'type'              => $data->type,
                'source'            => TimeEntrySourceEnum::MANUAL->value,
                'note'              => $data->note instanceof Optional ? null : $data->note,
            ]);

            $this->revertToDraft($day);
            $this->calculator->recalculate($day);

            return $timeEntry->fresh()->toResource();
        });
    }

    /**
     * Lança várias marcações de uma vez (ex.: "dia completo" a partir da
     * jornada). Agrupa por data, cria os dias necessários e recalcula cada
     * dia uma única vez. Com `$replace`, as marcações existentes de cada dia
     * afetado são substituídas pelas do lote.
     */
    public function createBatch(string $employeeId, array $entries, bool $replace = false)
    {
        $company  = $this->resolveCompany();
        $employee = $this->dayResolver->findEmployee($employeeId, $company->companyId);

        return DB::transaction(function () use ($entries, $company, $employee, $replace) {
            /** @var array<string, DailyEngagement> $days */
            $days = [];
            $seenPunchedAt = [];

            foreach ($entries as $entry) {
                $punchedAt = $this->normalizePunchedAt($entry['punchedAt']);
                $date      = substr($punchedAt, 0, 10);

                if (! isset($days[$date])) {
                    $days[$date] = $this->dayResolver->resolve(
                        $company->companyId,
                        $employee,
                        $date
                    );

                    if ($replace) {
                        $days[$date]->timeEntries()->delete();
                    }
                }

                // Sem `replace`, as marcações existentes do dia continuam de pé —
                // precisa checar contra o banco. Dentro do próprio lote (com ou
                // sem replace) duas entradas no mesmo horário também são inválidas.
                if (! $replace) {
                    $this->ensureNoConflictingPunch($employee->id, $punchedAt);
                }

                if (isset($seenPunchedAt[$punchedAt])) {
                    throw new DomainException('O lote tem duas marcações no mesmo horário.');
                }
                $seenPunchedAt[$punchedAt] = true;

                $day = $days[$date];

                $this->timeEntryRepository->create([
                    'companyId'         => $company->companyId,
                    'dailyEngagementId' => $day->id,
                    'punchedAt'        => $punchedAt,
                    'type'              => $entry['type'],
                    'source'            => TimeEntrySourceEnum::MANUAL->value,
                    'note'              => $entry['note'] ?? null,
                ]);
            }

            foreach ($days as $day) {
                $this->revertToDraft($day);
                $this->calculator->recalculate($day);
            }

            return array_map(
                fn (DailyEngagement $day) => $day->fresh(['timeEntries'])->toResource(),
                array_values($days)
            );
        });
    }

    public function update(TimeEntry $timeEntry, TimeEntryData $data)
    {
        return DB::transaction(function () use ($timeEntry, $data) {
            $payload = [];

            if (! ($data->punchedAt instanceof Optional)) {
                $punchedAt = $this->normalizePunchedAt($data->punchedAt);

                $this->ensureNoConflictingPunch(
                    $timeEntry->dailyEngagement->employeeId,
                    $punchedAt,
                    $timeEntry->id
                );

                $payload['punchedAt'] = $punchedAt;
            }

            if (! ($data->type instanceof Optional)) {
                $payload['type'] = $data->type;
            }

            if (! ($data->note instanceof Optional)) {
                $payload['note'] = $data->note;
            }

            $timeEntry->update($payload);

            $day = $timeEntry->dailyEngagement;
            $this->revertToDraft($day);
            $this->calculator->recalculate($day);

            return $timeEntry->fresh()->toResource();
        });
    }

    public function delete(TimeEntry $timeEntry): void
    {
        DB::transaction(function () use ($timeEntry) {
            $day = $timeEntry->dailyEngagement;

            $timeEntry->delete();

            $this->revertToDraft($day);
            $this->calculator->recalculate($day);
        });
    }

    public function list(int $perPage = 15)
    {
        $company = $this->resolveCompany();

        return $this->timeEntryRepository
            ->getModel()
            ->newQuery()
            ->where('companyId', $company->companyId)
            ->latest('punchedAt')
            ->paginate($perPage)
            ->through(fn (TimeEntry $timeEntry) => $timeEntry->toResource());
    }

    /**
     * Toda edição de marcação devolve o dia para rascunho (precisa reenviar/reaprovar)
     * e marca o autor atual como quem está draftando.
     */
    private function revertToDraft(DailyEngagement $day): void
    {
        if ($day->status === DailyEngagementStatusEnum::DRAFT) {
            return;
        }

        $day->forceFill([
            'status'     => DailyEngagementStatusEnum::DRAFT->value,
            'draftedBy'  => currentCompany()?->id,
            'approvedBy' => null,
            'approvedAt' => null,
        ])->save();
    }

    /**
     * Com CLT + diarista podendo coexistir no mesmo funcionário, o dia (por
     * funcionário+data) pode acabar recebendo marcações dos dois contextos —
     * bloqueia duas marcações no mesmo horário exato pro mesmo funcionário.
     */
    private function ensureNoConflictingPunch(
        string $employeeId,
        string $punchedAt,
        ?string $ignoreTimeEntryId = null
    ): void {
        $exists = TimeEntry::query()
            ->whereHas('dailyEngagement', fn ($q) => $q->where('employeeId', $employeeId))
            ->where('punchedAt', $punchedAt)
            ->when($ignoreTimeEntryId, fn ($q) => $q->where('id', '!=', $ignoreTimeEntryId))
            ->exists();

        if ($exists) {
            throw new DomainException('Já existe uma marcação para este funcionário nesse horário.');
        }
    }

    private function resolveCompany()
    {
        $company = currentCompany();

        if (! $company) {
            throw new \RuntimeException('Empresa não encontrada.');
        }

        return $company;
    }

    /**
     * O front envia a marcação com fuso (ex.: 2026-06-10T08:00:00-03:00) e o back
     * converte para UTC antes de gravar (08:00 -03:00 => 11:00 UTC). O front
     * converte de volta para o fuso local na exibição. Lançamento é retroativo —
     * a hora vem sempre do dado enviado, nunca do servidor.
     */
    private function normalizePunchedAt(string $value): string
    {
        return Carbon::parse($value)->utc()->format('Y-m-d H:i:s');
    }
}

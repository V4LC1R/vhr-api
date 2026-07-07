<?php

namespace Modules\Attendance\Services;

use DB;
use App\Contracts\DailyEngagementRepositoryInterface;
use App\Contracts\TimeEntryRepositoryInterface;
use Illuminate\Support\Carbon;
use Modules\Attendance\Data\TimeEntryData;
use Modules\Attendance\Enums\DailyEngagementStatusEnum;
use Modules\Attendance\Enums\DailyEngagementTypeEnum;
use Modules\Attendance\Enums\TimeEntrySourceEnum;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Attendance\Models\TimeEntry;
use Modules\Attendance\Support\AttendanceCalculator;
use Modules\Job\Models\Employee;
use Spatie\LaravelData\Optional;

class TimeEntryService
{
    public function __construct(
        protected TimeEntryRepositoryInterface $timeEntryRepository,
        protected DailyEngagementRepositoryInterface $dailyEngagementRepository,
        protected AttendanceCalculator $calculator,
    ) {
    }

    public function create(TimeEntryData $data)
    {
        $company   = $this->resolveCompany();
        $employee  = $this->findEmployee($data->employeeId, $company->companyId);
        $punchedAt = $this->normalizePunchedAt($data->punchedAt);
        $date      = substr($punchedAt, 0, 10);

        return DB::transaction(function () use ($data, $company, $employee, $date, $punchedAt) {
            $day = $this->resolveDay($company->companyId, $employee, $date);

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

    public function update(TimeEntry $timeEntry, TimeEntryData $data)
    {
        return DB::transaction(function () use ($timeEntry, $data) {
            $payload = [];

            if (! ($data->punchedAt instanceof Optional)) {
                $payload['punchedAt'] = $this->normalizePunchedAt($data->punchedAt);
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

    private function resolveDay(string $companyId, Employee $employee, string $date): DailyEngagement
    {
        $existing = $this->dailyEngagementRepository
            ->getModel()
            ->newQuery()
            ->where('companyId', $companyId)
            ->where('employeeId', $employee->id)
            ->whereDate('date', $date)
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->dailyEngagementRepository->create([
            'companyId'  => $companyId,
            'employeeId' => $employee->id,
            'workloadId' => $employee->activeEmployment?->workloadId,
            'date'       => $date,
            'type'       => DailyEngagementTypeEnum::WORK->value,
            'status'     => DailyEngagementStatusEnum::DRAFT->value,
            'draftedBy'  => currentCompany()?->id,
        ]);
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

    private function findEmployee(string $employeeId, string $companyId): Employee
    {
        $employee = Employee::query()// usar o bind employRepo()
            ->where('id', $employeeId)
            ->where('companyId', $companyId)
            ->with('activeEmployment')
            ->first();

        if (! $employee) {
            throw new \RuntimeException('Funcionário não encontrado na empresa atual.');
        }

        return $employee;
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

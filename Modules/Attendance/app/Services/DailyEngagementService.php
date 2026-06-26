<?php

namespace Modules\Attendance\Services;

use DB;
use App\Contracts\DailyEngagementRepositoryInterface;
use Modules\Attendance\Data\DailyEngagementData;
use Modules\Attendance\Enums\DailyEngagementStatusEnum;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Attendance\Queries\DailyEngagementListQuery;
use Modules\Attendance\Support\AttendanceCalculator;
use Spatie\LaravelData\Optional;

class DailyEngagementService
{
    public function __construct(
        protected DailyEngagementRepositoryInterface $dailyEngagementRepository,
        protected AttendanceCalculator $calculator,
    ) {
    }

    public function list(int $perPage = 15)
    {
        $company = $this->resolveCompany();

        $query = DailyEngagementListQuery::make($company->companyId);

        // Funcionário comum só lista os próprios dias.
        if ($this->isSelfServiceOnly()) {
            $query->whereHas(
                'employee',
                fn ($q) => $q->where('personId', $company->personId)
            );
        }

        // Rascunho só é visível para quem está draftando.
        $query->where(
            fn ($q) => $q
                ->where('status', '!=', DailyEngagementStatusEnum::DRAFT->value)
                ->orWhere('draftedBy', $company->id)
        );

        // Contador só enxerga dias aprovados (relatório de horas).
        if ($this->isAccountantOnly()) {
            $query->where('status', DailyEngagementStatusEnum::APPROVED->value);
        }

        return $query
            ->paginate($perPage)
            ->through(fn (DailyEngagement $day) => $day->toResource());
    }

    public function show(DailyEngagement $day)
    {
        return $day
            ->load(['timeEntries', 'employee.person', 'workload'])
            ->toResource();
    }

    public function upsertException(DailyEngagement $day, DailyEngagementData $data)
    {
        $company = $this->resolveCompany();

        return DB::transaction(function () use ($day, $data, $company) {
            $payload = [
                'type'   => $data->type,
                'status' => DailyEngagementStatusEnum::DRAFT->value,
            ];

            if (! ($data->note instanceof Optional)) {
                $payload['note'] = $data->note;
            }

            $day->forceFill($payload + [
                'draftedBy'  => $company->id,
                'approvedBy' => null,
                'approvedAt' => null,
            ])->save();

            $this->calculator->recalculate($day);

            return $day->fresh(['timeEntries'])->toResource();
        });
    }

    public function submit(DailyEngagement $day)
    {
        if ($day->status !== DailyEngagementStatusEnum::DRAFT) {
            throw new \RuntimeException('Apenas rascunhos podem ser enviados para aprovação.');
        }

        $day->forceFill([
            'status' => DailyEngagementStatusEnum::PENDING->value,
        ])->save();

        return $day->fresh(['timeEntries'])->toResource();
    }

    public function approve(DailyEngagement $day)
    {
        $this->ensurePending($day);

        $company = $this->resolveCompany();

        $day->forceFill([
            'status'     => DailyEngagementStatusEnum::APPROVED->value,
            'approvedBy' => $company->id,
            'approvedAt' => now()->utc(),
        ])->save();

        return $day->fresh(['timeEntries'])->toResource();
    }

    public function reject(DailyEngagement $day, ?string $note = null)
    {
        $this->ensurePending($day);

        $company = $this->resolveCompany();

        $day->forceFill([
            'status'     => DailyEngagementStatusEnum::REJECTED->value,
            'approvedBy' => $company->id,
            'approvedAt' => now()->utc(),
            'note'       => $note ?? $day->note,
        ])->save();

        return $day->fresh(['timeEntries'])->toResource();
    }

    private function ensurePending(DailyEngagement $day): void
    {
        if ($day->status !== DailyEngagementStatusEnum::PENDING) {
            throw new \RuntimeException('Apenas dias pendentes podem ser aprovados ou rejeitados.');
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

    private function isSelfServiceOnly(): bool
    {
        return ! currentCompany()?->hasRole('owner')
            && ! currentCompany()?->hasRole('humanResource')
            && ! currentCompany()?->hasRole('accountant');
    }

    /**
     * Contador (sem papel de gestão) só vê dias aprovados no relatório de horas.
     */
    private function isAccountantOnly(): bool
    {
        return currentCompany()?->hasRole('accountant')
            && ! currentCompany()?->hasRole('owner')
            && ! currentCompany()?->hasRole('humanResource');
    }
}

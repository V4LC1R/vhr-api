<?php

namespace Modules\Attendance\Support;

use App\Contracts\DailyEngagementRepositoryInterface;
use Modules\Attendance\Enums\DailyEngagementStatusEnum;
use Modules\Attendance\Enums\DailyEngagementTypeEnum;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Job\Models\Employee;

/**
 * Localiza (ou cria como rascunho) o dia de um funcionário numa data.
 * Compartilhado entre lançamento de marcações e exceções por data.
 */
class DayResolver
{
    public function __construct(
        protected DailyEngagementRepositoryInterface $dailyEngagementRepository,
    ) {
    }

    public function resolve(string $companyId, Employee $employee, string $date): DailyEngagement
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

    public function findEmployee(string $employeeId, string $companyId): Employee
    {
        $employee = Employee::query()
            ->where('id', $employeeId)
            ->where('companyId', $companyId)
            ->with('activeEmployment')
            ->first();

        if (! $employee) {
            throw new \RuntimeException('Funcionário não encontrado na empresa atual.');
        }

        return $employee;
    }
}

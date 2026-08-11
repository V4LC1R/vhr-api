<?php

namespace Modules\Job\Domain\Employment;

use App\Contracts\EmploymentRepositoryInterface;
use Modules\Job\Data\EmploymentData;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Spatie\LaravelData\Optional;

final class EmploymentBulkCreator
{
    public function __construct(
        protected EmploymentRepositoryInterface $employmentRepository,
        protected EmploymentEligibilityGuard $employmentEligibilityGuard,
    ) {
    }

    /**
     * @param array<EmploymentData> $data
     * @param Employee $employee
     */
    public function execute(array $data, Employee $employee)
    {

        $collect = collect($data);

        $this->ensures($collect, $employee);

        $data = $collect->map(function ($employmentData) use ($employee) {
            $kind = $this->resolveKind($employmentData);

            $status = ! ($employmentData->isProbationary instanceof Optional) && $employmentData->isProbationary
                ? EmploymentStatusEnum::EXPERIENCE->value
                : EmploymentStatusEnum::HIRED->value;

            return [
                'employeeId'  => $employee->id,
                'workloadId'  => $employmentData->workloadId,
                'kind'        => $kind,
                'status'      => $status,
                'registerAt' => now()->utc(),
            ];
        });

        return $this->employmentRepository->bulkCreate($data);
    }

    private function ensures($collect, Employee $employee)
    {
        $workloadsIds = $collect->pluck('workloadId')->all();

        $this
            ->employmentEligibilityGuard
            ->ensureBulkWorkloadBelongsToCompany($workloadsIds, $employee->companyId);

        $kinds = $collect->map(fn ($item) => $this->resolveKind($item))->all();

        $this
            ->employmentEligibilityGuard
            ->ensureBulkNoActiveEmployment($kinds, $employee->personId, $employee->companyId);
    }

    private function resolveKind(EmploymentData $data): string
    {
        return $data->kind instanceof Optional
            ? EmploymentTypeEnum::CLT->value
            : $data->kind;
    }
}

<?php

namespace Modules\Job\Domain\Employment;

use Modules\Job\Contract\EmploymentRepositoryInterface;
use Modules\Job\Contract\WorkloadRepositoryInterface;
use Modules\Job\Data\EmploymentData;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
use Spatie\LaravelData\Optional;

class EmploymentCreator
{
    public function __construct(
        protected EmploymentRepositoryInterface $employmentRepository,
        protected WorkloadRepositoryInterface $workloadRepository,
        protected EmploymentEligibilityGuard $employmentEligibilityGuard,
    ) {
    }

    public function execute(Employee $employee, EmploymentData $data): Employment
    {
        $workload = $this->workloadRepository->findById($data->workloadId);

        $kind = $data->kind instanceof Optional
            ? EmploymentTypeEnum::CLT->value
            : $data->kind;

        $this->employmentEligibilityGuard->ensureWorkloadBelongsToCompany($workload, $data->companyId);

        if ($data->kind == EmploymentTypeEnum::CLT->value) {
            $this
                ->employmentEligibilityGuard
                ->ensureNoActiveCltEmployment($data->companyId, $employee->personId);
        }

        return $this->employmentRepository->create([
            'companyId'   => $data->companyId,
            'employeeId'  => $employee->id,
            'workloadId'  => $data->workloadId,
            'kind'        => $kind,
            'status'      => ! ($data->isProbationary instanceof Optional) && $data->isProbationary
                ? EmploymentStatusEnum::EXPERIENCE->value
                : EmploymentStatusEnum::HIRED->value,
            'registerAt' => now()->utc(),
        ]);
    }
}

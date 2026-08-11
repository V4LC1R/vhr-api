<?php

namespace Modules\Job\Domain\Employment;

use App\Contracts\EmploymentRepositoryInterface;
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
        protected EmploymentEligibilityGuard $employmentEligibilityGuard,
    ) {
    }

    public function execute(Employee $employee, EmploymentData $data): Employment
    {
        $workload = $this->employmentEligibilityGuard->findWorkload($data->workloadId);

        $kind = $data->kind instanceof Optional
            ? EmploymentTypeEnum::CLT->value
            : $data->kind;

        $this->employmentEligibilityGuard->ensureWorkloadBelongsToCompany($workload, $employee->companyId);
        $this->employmentEligibilityGuard->ensureNoActiveEmployment($employee->companyId, $employee->personId, $kind);

        return $this->employmentRepository->create([
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

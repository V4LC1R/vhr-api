<?php

namespace Modules\Job\Domain\Employment;

use Modules\Job\Contract\EmploymentRepositoryInterface;
use Modules\Job\Models\Employee;
use Modules\Job\Data\EmploymentData;

class EmploymentBulkUpdater
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
}

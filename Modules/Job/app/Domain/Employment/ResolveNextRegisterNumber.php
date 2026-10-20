<?php

namespace Modules\Job\Domain\Employment;

use Modules\Job\Contract\EmployeeRepositoryInterface;

class ResolveNextRegisterNumber
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository,
    ) {
    }

    public function execute(string $companyId): int
    {
        return (
            $this->employeeRepository
                ->getModel()
                ->newQuery()
                ->where('companyId', $companyId)
                ->max('registerNumber')
            ?? 0
        ) + 1;
    }
}

<?php

namespace Modules\Job\UseCases\Employment;

use DB;
use Modules\Job\Contract\EmployeeRepositoryInterface;
use Modules\Job\Data\EmploymentData;
use Modules\Job\Domain\Employment\EmploymentCreator;
use Modules\Job\Models\Employment;

class CreateEmploymentUseCase
{
    public function __construct(
        protected EmploymentCreator $employementCreator,
        protected EmployeeRepositoryInterface $employeeRepository
    ) {
    }

    public function execute(EmploymentData $data): Employment
    {
        return DB::transaction(function () use ($data) {

            $employee = $this
                ->employeeRepository
                ->findByIdAndCompany($data->employeeId, $data->companyId);

            return $this
                ->employementCreator
                ->execute($employee, $data);
        });
    }
}

<?php

namespace Modules\Job\UseCases\Employee;

use Modules\Job\Data\UpdateEmployeeData;
use Modules\Job\Models\Employee;
use DB;
use Modules\Core\Contracts\PersonRepositoryInterface;
use Modules\Job\Domain\Employment\EmploymentBulkCreator;
use Modules\Job\Domain\Employment\EmploymentBulkUpdater;

class UpdateEmployeeUseCase
{
    public function __construct(
        protected EmploymentBulkCreator $bulkCreator,
        protected EmploymentBulkUpdater $bulkUpdater,
        protected PersonRepositoryInterface $personRepository,
    ) {
    }
    public function execute(Employee $employee, UpdateEmployeeData $data)
    {

        DB::transaction(function () use ($employee, $data) {

            $newEmployeeData = $employee->person()->fill($data->person);

            $this->personRepository->update($employee->personId, $newEmployeeData);

            return $employee
                ->fresh([
                    'activeEmployment.workload',
                    'person',
                    'employments' => fn ($query) => $query->latest('registerAt')->with('workload')
                ])
                ->toResource();
        });
    }
}

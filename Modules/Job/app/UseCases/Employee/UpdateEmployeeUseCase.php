<?php

namespace Modules\Job\UseCases\Employee;

use App\Contracts\PersonRepositoryInterface;
use Modules\Job\Data\UpdateEmployeeData;
use Modules\Job\Models\Employee;
use DB;
use Modules\Job\Domain\Employment\EmploymentBulkCreator;
use Modules\Job\Domain\Employment\EmploymentBulkUpdater;
use Spatie\LaravelData\Optional;

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
        DB::tranaction(function () use ($employee, $data) {
            $employment = $data->employment;

            $this->personRepository->update($employee->personId, $data->person);

            if (!($employment->add instanceof Optional)) {
                $this->bulkCreator->execute($employment->add, $employee);
            }

            if (!($employment->update instanceof Optional)) {
                $this->bulkUpdater->execute($employment->add, $employee);
            }



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

<?php

namespace Modules\Job\UseCases\Employment;

use Modules\Job\Data\EmploymentData;
use Modules\Job\Domain\Employment\EmploymentCreator;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;

class CreateEmploymentUseCase
{
    public function __construct(
        protected EmploymentCreator $employementCreator,
    ) {
    }

    public function execute(Employee $employee, EmploymentData $data): Employment
    {
        return $this->employementCreator->execute($employee, EmploymentData::from([
            'workloadId'     => $data->workloadId,
            'kind'           => $data->kind,
            'isProbationary' => $data->isProbationary,
        ]));
    }
}

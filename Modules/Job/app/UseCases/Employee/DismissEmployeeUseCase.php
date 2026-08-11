<?php

namespace Modules\Job\UseCases\Employee;

use DB;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Models\Employee;

class DismissEmployeeUseCase
{
    public function execute(Employee $employee)
    {
        $employee->loadMissing('activeEmployment');
        $activeEmployment = $employee->activeEmployment;

        if (! $activeEmployment) {
            throw new \RuntimeException('O funcionário já foi desligado.');
        }

        return DB::transaction(function () use ($activeEmployment, $employee) {
            $activeEmployment->update([
                'status'  => EmploymentStatusEnum::LEFT->value,
                'leftAt' => now()->utc(),
            ]);

            return $employee
                ->fresh(['activeEmployment.workload', 'person'])
                ->toResource();
        });
    }
}

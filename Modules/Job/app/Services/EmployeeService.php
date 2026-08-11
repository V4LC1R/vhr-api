<?php

namespace Modules\Job\Services;

use DB;
use Modules\Job\Data\EmployeeData;
use Modules\Job\Data\UpdateEmployeeData;
use Modules\Job\Models\Employee;
use Modules\Job\Queries\EmployeeListQuery;
use Modules\Job\UseCases\Employee\CreateEmployeeUseCase;
use Modules\Job\UseCases\Employee\DismissEmployeeUseCase;
use Modules\Job\UseCases\Employee\ResolveNextRegisterNumberUseCase;
use Modules\Job\UseCases\Employee\UpdateEmployeeUseCase;

class EmployeeService
{
    public function __construct(
        protected CreateEmployeeUseCase $createEmployeeUseCase,
        protected DismissEmployeeUseCase $dismissEmployeeUseCase,
        protected UpdateEmployeeUseCase $updateEmployeeUseCase,
        protected ResolveNextRegisterNumberUseCase $resolveNextRegisterNumberUseCase,
    ) {
    }

    public function create(EmployeeData $data)
    {
        return $this->createEmployeeUseCase->execute($data);
    }

    public function update(Employee $employee, UpdateEmployeeData $data)
    {
        $this->updateEmployeeUseCase->execute($employee, $data);
    }

    public function dismiss(Employee $employee)
    {
        return $this->dismissEmployeeUseCase->execute($employee);
    }

    public function delete(Employee $employee): void
    {
        DB::transaction(fn () => $employee->delete());
    }

    public function list(int $perPage = 15)
    {
        return EmployeeListQuery::make()
            ->paginate($perPage)
            ->through(fn (Employee $employee) => $employee->toResource());
    }

    public function previewNextRegisterNumber(): int
    {
        $company = currentCompany();

        if (! $company) {
            throw new \RuntimeException('Empresa não encontrada.');
        }

        return $this->resolveNextRegisterNumberUseCase->execute($company->companyId);
    }
}

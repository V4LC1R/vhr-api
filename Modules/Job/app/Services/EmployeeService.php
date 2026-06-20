<?php

namespace Modules\Job\Services;

use DB;
use App\Exceptions\UniqueConstraintException;
use App\Helpers\DatabaseExceptionResolver;
use Illuminate\Database\QueryException;
use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\CompanyRepositoryInterface;
use App\Contracts\PersonRepositoryInterface;
use App\Contracts\WorkloadRepositoryInterface;
use Modules\Job\Data\EmployeeData;
use Modules\Job\Enums\EmployeeRoleEnum;
use Modules\Job\Enums\EmployeeStatusEnum;
use Modules\Job\Http\Resources\EmployeeResource;
use Modules\Job\Models\Employee;
use Modules\Job\Queries\EmployeeListQuery;

class EmployeeService
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository,
        protected CompanyRepositoryInterface $companyRepository,
        protected PersonRepositoryInterface $personRepository,
        protected WorkloadRepositoryInterface $workloadRepository,
    ) {
    }

    public function create(EmployeeData $data)
    {
        $company = $this->findCompany($data->companyId);
        $person = $this->findPerson($data->personId);
        $workload = $this->findWorkload($data->workloadId);

        $this->ensureWorkloadBelongsToCompany($workload, $company->id);
        $this->ensureNoActiveEmployee($company->id, $person->id);

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return DB::transaction(function () use ($data) {
                    $employee = $this->employeeRepository
                        ->create([
                            'companyId' => $data->companyId,
                            'personId' => $data->personId,
                            'workloadId' => $data->workloadId,

                            'registerNumber' => $this->getNextRegisterNumber(
                                $data->companyId
                            ),

                            'status' => EmployeeStatusEnum::EXPERIENCE,
                            'role' => EmployeeRoleEnum::EMPLOYEE,

                            'register_at' => now()->utc(),
                        ]);

                    return $employee
                        ->fresh()
                        ->toResource();
                });
            } catch (QueryException $e) {
                if (!DatabaseExceptionResolver::isUniqueViolation($e)) {
                    throw $e;
                }

                if ($attempt === $maxAttempts) {
                    throw new UniqueConstraintException(
                        'Já existe um funcionário cadastrado com os dados informados.',
                        previous: $e
                    );
                }
            }
        }
    }

    public function update(
        Employee $employee,
        EmployeeData $data
    ) {
        $workload = $this->findWorkload(
            $data->workloadId
        );

        $this->ensureWorkloadBelongsToCompany(
            $workload,
            $employee->companyId,
            'A jornada não pertence à empresa do funcionário.'
        );

        if (
            $employee->status === EmployeeStatusEnum::OUT
            && $data->status !== EmployeeStatusEnum::OUT
        ) {
            throw new \RuntimeException(
                'Não é possível reativar um funcionário desligado.'
            );
        }

        $employee->update([
            'status' => $data->status,
            'role' => $data->role,
            'workloadId' => $data->workloadId,
        ]);

        return $employee
        ->fresh()
        ->toResource();
    }

    public function dismiss(
        Employee $employee
    ) {
        if (
            $employee->status === EmployeeStatusEnum::OUT
        ) {
            throw new \RuntimeException(
                'O funcionário já foi desligado.'
            );
        }

        return DB::transaction(
            function () use ($employee) {

                $employee->update([
                    'status' => EmployeeStatusEnum::OUT,
                    'left_at' => now()->utc(),
                ]);

                return $employee
                    ->fresh()
                    ->toResource();
            }
        );
    }

    public function delete(
        Employee $employee
    ): void {
        DB::transaction(
            fn () => $employee->delete()
        );
    }

    public function list(
        int $perPage = 15
    ) {
        $employees = EmployeeListQuery::make()
            ->paginate(
                $perPage
            );

        return EmployeeResource::collection(
            $employees
        );
    }

    private function findCompany(
        string $companyId
    ) {
        $company = $this->companyRepository
            ->findById($companyId);

        if (! $company) {
            throw new \RuntimeException(
                'Empresa não encontrada.'
            );
        }

        return $company;
    }

    private function findPerson(
        string $personId
    ) {
        $person = $this->personRepository
            ->findById($personId);

        if (! $person) {
            throw new \RuntimeException(
                'Pessoa não encontrada.'
            );
        }

        return $person;
    }

    private function findWorkload(
        string $workloadId
    ) {
        $workload = $this->workloadRepository
            ->findById($workloadId);

        if (! $workload) {
            throw new \RuntimeException(
                'Jornada não encontrada.'
            );
        }

        return $workload;
    }

    private function ensureWorkloadBelongsToCompany(
        $workload,
        string $companyId,
        string $message = 'A jornada não pertence à empresa informada.'
    ): void {
        if ($workload->companyId !== $companyId) {
            throw new \RuntimeException(
                $message
            );
        }
    }

    private function ensureNoActiveEmployee(
        string $companyId,
        string $personId
    ): void {
        if (
            $this->hasActiveEmployee(
                $companyId,
                $personId
            )
        ) {
            throw new UniqueConstraintException(
                'Esta pessoa já possui vínculo ativo com a empresa.'
            );
        }
    }

    private function hasActiveEmployee(
        string $companyId,
        string $personId
    ): bool {
        return $this->employeeRepository
            ->getModel()
            ->newQuery()
            ->where('companyId', $companyId)
            ->where('personId', $personId)
            ->where(
                'status',
                '!=',
                EmployeeStatusEnum::OUT
            )
            ->exists();
    }

    private function getNextRegisterNumber(
        string $companyId
    ): int {
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

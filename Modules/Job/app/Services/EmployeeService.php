<?php

namespace Modules\Job\Services;

use DB;
use App\Contracts\CompanyRepositoryInterface;
use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\EmploymentRepositoryInterface;
use App\Contracts\PersonRepositoryInterface;
use App\Contracts\WorkloadRepositoryInterface;
use App\Exceptions\UniqueConstraintException;
use App\Helpers\DatabaseExceptionResolver;
use Illuminate\Database\QueryException;
use Modules\Job\Data\EmployeeData;
use Modules\Job\Enums\EmploymentStatusEnum;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Queries\EmployeeListQuery;
use Spatie\LaravelData\Optional;

class EmployeeService
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository,
        protected EmploymentRepositoryInterface $employmentRepository,
        protected CompanyRepositoryInterface $companyRepository,
        protected PersonRepositoryInterface $personRepository,
        protected WorkloadRepositoryInterface $workloadRepository,
    ) {
    }

    public function create(EmployeeData $data)
    {
        $company  = $this->findCompany($data->companyId);
        $person   = $this->findPerson($data->personId);
        $workload = $this->findWorkload($data->workloadId);

        $this->ensureWorkloadBelongsToCompany($workload, $company->id);
        $this->ensureNoActiveEmployment($company->id, $person->id);

        return DB::transaction(function () use ($data, $company, $person) {
            $employee = $this->resolveOrCreateEmployee($company->id, $person->id);

            $this->employmentRepository->create([
                'employeeId'  => $employee->id,
                'workloadId'  => $data->workloadId,
                'kind'        => $data->kind instanceof Optional
                    ? EmploymentTypeEnum::CLT->value
                    : $data->kind,
                'status'      => EmploymentStatusEnum::EXPERIENCE->value,
                'registerAt' => now()->utc(),
            ]);

            return $employee
                ->fresh(['activeEmployment.workload', 'person'])
                ->toResource();
        });
    }

    public function update(Employee $employee, EmployeeData $data)
    {
        $workload = $this->findWorkload($data->workloadId);

        $this->ensureWorkloadBelongsToCompany(
            $workload,
            $employee->companyId,
            'A jornada não pertence à empresa do funcionário.'
        );

        $employee->loadMissing('activeEmployment');
        $activeEmployment = $employee->activeEmployment;

        if (! $activeEmployment) {
            throw new \RuntimeException('O funcionário não possui vínculo ativo.');
        }

        $activeEmployment->update([
            'status'     => $data->status instanceof Optional
                ? $activeEmployment->status->value
                : $data->status,
            'workloadId' => $data->workloadId,
        ]);

        return $employee
            ->fresh(['activeEmployment.workload', 'person'])
            ->toResource();
    }

    public function dismiss(Employee $employee)
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

    private function resolveOrCreateEmployee(string $companyId, string $personId): Employee
    {
        $employee = Employee::query()
            ->where('companyId', $companyId)
            ->where('personId', $personId)
            ->first();

        if ($employee) {
            return $employee;
        }

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return $this->employeeRepository->create([
                    'companyId'      => $companyId,
                    'personId'       => $personId,
                    'registerNumber' => $this->getNextRegisterNumber($companyId),
                ]);
            } catch (QueryException $e) {
                if (! DatabaseExceptionResolver::isUniqueViolation($e)) {
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

    private function findCompany(string $companyId)
    {
        $company = $this->companyRepository->findById($companyId);

        if (! $company) {
            throw new \RuntimeException('Empresa não encontrada.');
        }

        return $company;
    }

    private function findPerson(string $personId)
    {
        $person = $this->personRepository->findById($personId);

        if (! $person) {
            throw new \RuntimeException('Pessoa não encontrada.');
        }

        return $person;
    }

    private function findWorkload(string $workloadId)
    {
        $workload = $this->workloadRepository->findById($workloadId);

        if (! $workload) {
            throw new \RuntimeException('Jornada não encontrada.');
        }

        return $workload;
    }

    private function ensureWorkloadBelongsToCompany(
        $workload,
        string $companyId,
        string $message = 'A jornada não pertence à empresa informada.'
    ): void {
        if ($workload->companyId !== $companyId) {
            throw new \RuntimeException($message);
        }
    }

    private function ensureNoActiveEmployment(string $companyId, string $personId): void
    {
        $hasActiveClt = $this->employeeRepository
            ->getModel()
            ->newQuery()
            ->where('companyId', $companyId)
            ->where('personId', $personId)
            ->whereHas('employments', fn ($q) => $q
                ->where('kind', EmploymentTypeEnum::CLT->value)
                ->whereIn('status', [
                    EmploymentStatusEnum::HIRED->value,
                    EmploymentStatusEnum::EXPERIENCE->value,
                ]))
            ->exists();

        if ($hasActiveClt) {
            throw new UniqueConstraintException(
                'Esta pessoa já possui vínculo CLT ativo com a empresa.'
            );
        }
    }

    private function getNextRegisterNumber(string $companyId): int
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

    public function previewNextRegisterNumber(): int
    {
        $company = currentCompany();

        if (! $company) {
            throw new \RuntimeException('Empresa não encontrada.');
        }

        return $this->getNextRegisterNumber($company->companyId);
    }
}

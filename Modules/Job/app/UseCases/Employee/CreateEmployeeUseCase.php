<?php

namespace Modules\Job\UseCases\Employee;

use DB;
use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\EmploymentRepositoryInterface;
use App\Exceptions\UniqueConstraintException;
use App\Helpers\DatabaseExceptionResolver;
use Illuminate\Database\QueryException;
use Modules\Core\Contracts\CompanyRepositoryInterface;
use Modules\Core\Contracts\PersonRepositoryInterface;
use Modules\Core\Contracts\PersonServiceInterface;
use Modules\Job\Data\EmployeeData;
use Modules\Job\Data\EmploymentData;
use Modules\Job\Domain\Employment\EmploymentCreator;
use Modules\Job\Domain\Employment\ResolveNextRegisterNumber;
use Modules\Job\Models\Employee;
use Spatie\LaravelData\Optional;

class CreateEmployeeUseCase
{
    public function __construct(
        protected EmployeeRepositoryInterface $employeeRepository,
        protected CompanyRepositoryInterface $companyRepository,
        protected PersonRepositoryInterface $personRepository,
        protected EmploymentRepositoryInterface $employmentRepository,
        protected PersonServiceInterface $personService,
        protected EmploymentCreator $employementCreator,
        protected ResolveNextRegisterNumber $nextRegister
    ) {
    }

    public function execute(EmployeeData $data)
    {
        $company = $this->findCompany($data->companyId);
        $person  = $data->personId instanceof Optional
            ? $this->personService->create($data->person)
            : $this->findPerson($data->personId);

        return DB::transaction(function () use ($data, $company, $person) {
            //buscar pelo cpf e substituir os dados pelos novos
            //tem q fazer o treo da lgpd
            $employee = $this->resolveOrCreateEmployee($company->id, $person->id);

            $this->employementCreator->execute($employee, EmploymentData::from([
                'workloadId'     => $data->workloadId,
                'kind'           => $data->kind,
                'isProbationary' => $data->isProbationary,
            ]));

            return $employee
                ->fresh(['activeEmployment.workload', 'person'])
                ->toResource();
        });
    }

    private function resolveOrCreateEmployee(string $companyId, string $personId): Employee
    {
        $employee = $this
            ->employmentRepository
            ->findByCompanyAndPerson($companyId, $personId);

        if ($employee) {
            return $employee;
        }

        $maxAttempts = 3;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return $this->employeeRepository->create([
                    'companyId'      => $companyId,
                    'personId'       => $personId,
                    'registerNumber' => $this->nextRegister->execute($companyId),
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
}

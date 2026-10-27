<?php

namespace Modules\Job\UseCases\Employee;

use DB;
use App\Exceptions\UniqueConstraintException;
use App\Helpers\DatabaseExceptionResolver;
use Illuminate\Database\QueryException;
use Modules\Core\Contracts\CompanyRepositoryInterface;
use Modules\Core\Contracts\PersonRepositoryInterface;
use Modules\Core\Contracts\PersonServiceInterface;
use Modules\Job\Contract\EmployeeRepositoryInterface;
use Modules\Job\Contract\EmploymentRepositoryInterface;
use Modules\Job\Data\EmployeeData;
use Modules\Job\Data\EmploymentData;
use Modules\Job\Domain\Employment\EmploymentCreator;
use Modules\Job\Domain\Employment\ResolveNextRegisterNumber;
use Modules\Job\Models\Employee;

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
        return DB::transaction(function () use ($data) {

            $company = $this->findCompany($data->companyId);
            $person  = $this->resolvePerson($data->person);

            $employee = $this->resolveOrCreateEmployee($person->id, $company->id,);

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

    private function resolveOrCreateEmployee(string $personId, string $companyId): Employee
    {
        $employee = $this
            ->employeeRepository
            ->findByPersonIdAndCompany($personId, $companyId);

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

    private function resolvePerson(array $data)
    {
        //trocar para o findOrCreate
        $person = $this->personRepository->findByCPF($data['cpf']);

        if (! $person) {
            return $this->personRepository->create($data);
        }

        $person->fill($data);
        $person->save();

        return $person;
    }
}

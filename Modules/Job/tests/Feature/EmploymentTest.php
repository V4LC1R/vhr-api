<?php

namespace Modules\Job\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
use Modules\Job\Models\Workload;
use Tests\DBTestCase;

class EmploymentTest extends DBTestCase
{
    protected bool $seed = true;

    private function criarDadosFuncionario(): array
    {
        $company = Company::factory()->create();

        $person = Person::factory()->create();

        $workload = Workload::factory()->create([
            'companyId' => $company->id,
        ]);

        return compact('company', 'person', 'workload');
    }

    private function criarFuncionarioComVinculo(?Company $company = null): array
    {
        $employee = Employee::factory()->create(
            $company ? ['companyId' => $company->id] : []
        );

        $workload = Workload::factory()->create(['companyId' => $employee->companyId]);

        Employment::factory()->create([
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
        ]);

        return compact('employee', 'workload');
    }

    //tem rota de novo vinculo
    //rota de edicao de vinculo
    //rota de encerramento de vinculo
}

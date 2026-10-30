<?php

namespace Modules\Job\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
use Modules\Job\Models\Workload;
use Tests\DBTestCase;

class EmployeeTest extends DBTestCase
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
            'companyId' => $employee->companyId,
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
        ]);

        return compact('employee', 'workload');
    }

    public function testUsuarioComPermissaoPodeContratarFuncionario(): void
    {
        $data = $this->criarDadosFuncionario();

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $data['company'],
        );

        $cpf = fake('pt_BR')->unique()->cpf(false);

        $response = $this->postJson(
            '/api/v1/employees',
            [
                'employment' => [
                    'workloadId'     => $data['workload']->id,
                    'kind'           => EmploymentTypeEnum::CLT->value,
                    'isProbationary' => true,
                ],
                'person' => [
                    'name' => 'vasco',
                    'email' => 'vasco@gama.com',
                    'cellphone' => '6799999999',
                    'birthdate' => now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP'),
                    'pixKey' => 'xxx-xxx-xxx-xxx',
                    'cpf' => $cpf
                ]
            ]
        );

        $response->assertCreated();

        $this->assertDatabaseHas('core.persons', [
            'cpf' => $cpf
        ]);
    }

    public function testContratacaoSemExperienciaComecaComoContratado(): void
    {
        $data = $this->criarDadosFuncionario();

        $personData = $data['person']->toArray();
        $personData['birthdate'] = now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP');
        $personData['pixKey']    = fake()->unique()->safeEmail();
        $idPerson = $personData['id'];


        unset($personData['id']);
        unset($personData['updated_at']);
        unset($personData['created_at']);

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $data['company'],
        );

        $response = $this->postJson(
            '/api/v1/employees',
            [

                'employment' => [
                    'workloadId'     => $data['workload']->id,
                    'kind'           => EmploymentTypeEnum::CLT->value,
                    'isProbationary' => false,
                ],
                'person' => $personData
            ]
        );

        $response->assertCreated();

        $employee = Employee::query()
            ->where('companyId', $data['company']->id)
            ->where('personId', $idPerson)
            ->firstOrFail();

        $this->assertDatabaseHas('job.employments', [
            'employeeId' => $employee->id,
            'status'     => 'hired',
            'workloadId' => $data['workload']->id,
        ]);
    }

    public function testUsuarioComPermissaoPodeContratarFuncionarioComPessoaNova(): void
    {
        $company  = Company::factory()->create();
        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $company,
        );

        $novaPessoa = [
            'cpf'       => fake('pt_BR')->unique()->cpf(false),
            'name'      => 'Fulano de Tal',
            'email'     => fake()->unique()->safeEmail(),
            'cellphone' => '11999999999',
            'pixKey'    => fake()->unique()->safeEmail(),
            'birthdate' => now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP')
        ];

        $response = $this->postJson(
            '/api/v1/employees',
            [
                'person'         => $novaPessoa,
                'employment'     => [
                    'workloadId'     => $workload->id,
                    'kind'           => EmploymentTypeEnum::CLT->value,
                    'isProbationary' => true,
                ]
            ]
        );

        $response->assertCreated();

        $person = Person::query()->where('cpf', $novaPessoa['cpf'])->firstOrFail();

        $this->assertDatabaseHas('core.persons', [
            'cpf'   => $novaPessoa['cpf'],
            'name'  => $novaPessoa['name'],
            'email' => $novaPessoa['email'],
        ]);

        $this->assertDatabaseHas('job.employees', [
            'companyId' => $company->id,
            'personId'  => $person->id,
        ]);
    }

    public function testNaoPermiteContratarSemPersonNemPessoaNova(): void
    {
        $company  = Company::factory()->create();
        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $company,
        );

        $this->postJson(
            '/api/v1/employees',
            [
                'employment'     => [
                    'workloadId'     => $workload->id,
                    'kind'           => EmploymentTypeEnum::CLT->value,
                    'isProbationary' => true,
                ]
            ]
        )->assertUnprocessable()
            ->assertJsonValidationErrors([
                'person.pixKey',
                'person.cpf',
                'person.name',
                'person.email',
                'person.cellphone',
            ]);
    }


    public function testUsuarioSemPermissaoNaoPodeContratarFuncionario(): void
    {
        $this->autenticarSemPermissao();

        $data = $this->criarDadosFuncionario();

        $this->postJson(
            '/api/v1/employees',
            [
                'employment' => [
                    'workloadId'     => $data['workload']->id,
                    'kind'           => EmploymentTypeEnum::CLT->value,
                    'isProbationary' => true,
                ],
                'person' => [
                    'name' => 'vasco',
                    'email' => 'vasco@gama.com',
                    'cellphone' => '6799999999',
                    'birthdate' => now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP'),
                    'pixKey' => 'xxx-xxx-xxx-xxx',
                    'cpf' => fake('pt_BR')->unique()->cpf(false)
                ]
            ]
        )->assertForbidden();
    }

    public function testGeraMatriculaSequencialPorEmpresa(): void
    {
        $data = $this->criarDadosFuncionario();

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $data['company'],
        );

        $this->postJson(
            '/api/v1/employees',
            [
                'employment'     => [
                    'workloadId'     => $data['workload']->id,
                    'kind'           => EmploymentTypeEnum::CLT->value,
                    'isProbationary' => true,
                ],
                'person' => [
                    'name' => 'vasco',
                    'email' => 'vasco@gama.com',
                    'cellphone' => '6799949999',
                    'birthdate' => now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP'),
                    'pixKey' => 'xxx-xxx-xxx-xxx',
                    'cpf' => fake('pt_BR')->unique()->cpf(false)
                ]
            ]
        )->assertCreated();

        $person2 = Person::factory()->create();

        $this->postJson(
            '/api/v1/employees',
            [
                'employment'     => [
                    'workloadId'     => $data['workload']->id,
                    'kind'           => EmploymentTypeEnum::CLT->value,
                    'isProbationary' => true,
                ],
                'person' => [
                    'name' => 'vasco',
                    'email' => 'gigante@dacolina.com',
                    'cellphone' => '6799997999',
                    'birthdate' => now()->timezone('Europe/Stockholm')->format('Y-m-d\TH:i:sP'),
                    'pixKey' => 'xxx-xxx-xxx-xxx',
                    'cpf' => fake('pt_BR')->unique()->cpf(false)
                ]
            ]
        )->assertCreated();

        $registerNumbers = Employee::query()
            ->where('companyId', $data['company']->id)
            ->orderBy('registerNumber')
            ->pluck('registerNumber')
            ->values()
            ->all();

        $this->assertEquals([1, 2], $registerNumbers);
    }

    public function testNaoPermiteVinculoAtivoDuplicado(): void
    {
        $company  = Company::factory()->create();
        $person   = Person::factory()->create();

        $workload = Workload::factory()->create(['companyId' => $company->id]);
        $employee = Employee::factory()->create(['companyId' => $company->id,'personId' => $person->id]);

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $company,
        );

        $personData = $person->toArray();
        $personData['pixKey']    = fake()->unique()->safeEmail();

        unset($personData['id']);
        unset($personData['updated_at']);
        unset($personData['created_at']);
        unset($personData['updated_at']);

        Employment::factory()->create([
            'companyId' => $company->id,
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
        ]);

        $this->postJson(
            '/api/v1/employees',
            [
                'employment'     => [
                    'workloadId'     => $workload->id,
                    'kind'           => EmploymentTypeEnum::CLT->value,
                    'isProbationary' => true,
                ],
                'person' => $personData
            ]
        )->assertConflict();
    }

    public function testPermiteContratarMesmaPersonComVinculoNaoCltAtivo(): void
    {
        $company  = Company::factory()->create();
        $person   = Person::factory()->create();

        $workload = Workload::factory()->create(['companyId' => $company->id]);
        $workload2 = Workload::factory()->create(['companyId' => $company->id]);

        $employee = Employee::factory()->create(['companyId' => $company->id,'personId' => $person->id]);

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $company,
        );

        Employment::factory()->create([
            'companyId' => $company->id,
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
            'kind'       => EmploymentTypeEnum::FREELANCER->value,
        ]);

        $personData = $person->toArray();
        $personData['pixKey']    = fake()->unique()->safeEmail();

        unset($personData['id']);
        unset($personData['updated_at']);
        unset($personData['created_at']);
        unset($personData['updated_at']);

        $this->postJson('/api/v1/employees', [
            'employment'     => [
                'workloadId'     => $workload2->id,
                'kind'           => EmploymentTypeEnum::CLT->value,
                'isProbationary' => true,
            ],
            'person' => $personData
        ])->assertCreated();
    }

    public function testPermiteContratarComoDiaristaMesmoComCltAtivo(): void
    {
        $company  = Company::factory()->create();
        $person   = Person::factory()->create();

        $workload  = Workload::factory()->create(['companyId' => $company->id]);
        $workload2 = Workload::factory()->create(['companyId' => $company->id]);

        $employee  = Employee::factory()->create(['companyId' => $company->id, 'personId' => $person->id]);

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $company,
        );

        Employment::factory()->create([
            'companyId' => $company->id,
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
            'kind'       => EmploymentTypeEnum::CLT->value,
        ]);

        $personData = $person->toArray();
        $personData['pixKey']    = fake()->unique()->safeEmail();

        unset($personData['id']);
        unset($personData['updated_at']);
        unset($personData['created_at']);
        unset($personData['updated_at']);

        // CLT ativo não deve impedir um vínculo dayli/temporary/freelancer concorrente
        // — só bloqueia CLT duplicado (ver testNaoPermiteVinculoAtivoDuplicado).
        $this->postJson('/api/v1/employees', [
            'employment'     => [
                'workloadId'     => $workload2->id,
                'kind'           => EmploymentTypeEnum::DAYLI->value,
                'isProbationary' => false,
            ],
            'person' => $personData
        ])->assertCreated();

        $this->assertDatabaseHas('job.employments', [
            'workloadId'     => $workload2->id,
            'employeeId' => $employee->id,
            'kind'       => EmploymentTypeEnum::DAYLI->value,
        ]);
    }

    public function testPermiteRecontratacao(): void
    {
        $company  = Company::factory()->create();
        $person   = Person::factory()->create();

        $workload = Workload::factory()->create(['companyId' => $company->id]);
        $employee = Employee::factory()->create(['companyId' => $company->id, 'personId' => $person->id]);

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $company,
        );

        Employment::factory()->left()->create([
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
        ]);

        $employee->loadMissing('person');

        $person = $employee->person->toArray();
        $person['pixKey']    = fake()->unique()->safeEmail();

        unset($person['id']);
        unset($person['updated_at']);
        unset($person['created_at']);
        unset($person['updated_at']);

        $this->postJson(
            '/api/v1/employees',
            [
                'person'         => $person,
                'employment'     => [
                    'workloadId'     => $workload->id,
                    'kind'           => EmploymentTypeEnum::CLT->value,
                    'isProbationary' => true,
                ]
            ]
        )->assertCreated();

        $this->assertEquals(
            1,
            Employee::query()
                ->where('companyId', $employee->companyId)
                ->where('personId', $employee->personId)
                ->count()
        );

        $this->assertEquals(2, $employee->employments()->count());
    }

    public function testUsuarioComPermissaoPodeVisualizarFuncionario(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComPermissao('job.employees.view', company: $company);

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        $this->getJson("/api/v1/employees/{$employee->id}")->assertOk();
    }

    public function testNaoAcessaFuncionarioDeOutraEmpresa(): void
    {
        $companyA = Company::factory()->create();

        $this->autenticarComPermissao('job.employees.view', company: $companyA);

        $outraEmpresa = Company::factory()->create();
        $employee     = Employee::factory()->create(['companyId' => $outraEmpresa->id]);

        $this->getJson("/api/v1/employees/{$employee->id}")->assertNotFound();
    }

    public function testUsuarioComPermissaoPodeListarFuncionarios(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComPermissao('job.employees.view', company: $company);

        Employee::factory()->count(3)->create(['companyId' => $company->id]);

        $this->getJson('/api/v1/employees')->assertOk();
    }

    public function testOwnerPodeDemitirFuncionario(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('owner', company: $company);

        ['employee' => $employee] = $this->criarFuncionarioComVinculo($company);

        $this->patchJson("/api/v1/employees/{$employee->id}/dismiss")->assertOk();

        $latestEmployment = $employee->employments()->latest()->first();

        $this->assertEquals('left', $latestEmployment->status->value);
        $this->assertNotNull($latestEmployment->leftAt);
    }

    public function testRhPodeDemitirOutroFuncionario(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('humanResource', company: $company);

        ['employee' => $employee] = $this->criarFuncionarioComVinculo($company);

        $this->patchJson("/api/v1/employees/{$employee->id}/dismiss")->assertOk();
    }

    public function testRhNaoPodeDemitirASiMesmo(): void
    {
        $company = Company::factory()->create();
        $person  = Person::factory()->create();

        $this->autenticarComRole('humanResource', company: $company, person: $person);

        $employee = Employee::factory()->create([
            'companyId' => $company->id,
            'personId'  => $person->id,
        ]);

        $this->patchJson("/api/v1/employees/{$employee->id}/dismiss")->assertForbidden();
    }

    public function testFuncionarioComumNaoPodeDemitir(): void
    {
        $company = Company::factory()->create();

        $this->autenticarSemPermissao(company: $company);

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        $this->patchJson("/api/v1/employees/{$employee->id}/dismiss")->assertForbidden();
    }

    public function testOwnerPodeExcluirFuncionario(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('owner', company: $company);

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        $this->deleteJson("/api/v1/employees/{$employee->id}")->assertNoContent();

        $this->assertDatabaseMissing('job.employees', ['id' => $employee->id]);
        $this->assertDatabaseMissing('job.employments', ['employeeId' => $employee->id]);
    }

    public function testRhPodeExcluirOutroFuncionario(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('humanResource', company: $company);

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        $this->deleteJson("/api/v1/employees/{$employee->id}")->assertNoContent();
    }

    public function testRhNaoPodeExcluirASiMesmo(): void
    {
        $company = Company::factory()->create();
        $person  = Person::factory()->create();

        $this->autenticarComRole('humanResource', company: $company, person: $person);

        $employee = Employee::factory()->create([
            'companyId' => $company->id,
            'personId'  => $person->id,
        ]);

        $this->deleteJson("/api/v1/employees/{$employee->id}")->assertForbidden();
    }

    public function testFuncionarioComumNaoPodeExcluir(): void
    {
        $company = Company::factory()->create();

        $this->autenticarSemPermissao(company: $company);

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        $this->deleteJson("/api/v1/employees/{$employee->id}")->assertForbidden();
    }
}

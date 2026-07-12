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

        $response = $this->postJson(
            '/api/v1/employees',
            [
                'companyId'      => $data['company']->id,
                'personId'       => $data['person']->id,
                'workloadId'     => $data['workload']->id,
                'kind'           => EmploymentTypeEnum::CLT->value,
                'isProbationary' => true,
            ]
        );

        $response->assertCreated();

        $employee = Employee::query()
            ->where('companyId', $data['company']->id)
            ->where('personId', $data['person']->id)
            ->firstOrFail();

        $this->assertDatabaseHas('job.employments', [
            'employeeId' => $employee->id,
            'status'     => 'experience',
            'workloadId' => $data['workload']->id,
        ]);
    }

    public function testContratacaoSemExperienciaComecaComoContratado(): void
    {
        $data = $this->criarDadosFuncionario();

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $data['company'],
        );

        $response = $this->postJson(
            '/api/v1/employees',
            [
                'companyId'      => $data['company']->id,
                'personId'       => $data['person']->id,
                'workloadId'     => $data['workload']->id,
                'kind'           => EmploymentTypeEnum::CLT->value,
                'isProbationary' => false,
            ]
        );

        $response->assertCreated();

        $employee = Employee::query()
            ->where('companyId', $data['company']->id)
            ->where('personId', $data['person']->id)
            ->firstOrFail();

        $this->assertDatabaseHas('job.employments', [
            'employeeId' => $employee->id,
            'status'     => 'hired',
            'workloadId' => $data['workload']->id,
        ]);
    }

    public function testUsuarioSemPermissaoNaoPodeContratarFuncionario(): void
    {
        $this->autenticarSemPermissao();

        $data = $this->criarDadosFuncionario();

        $this->postJson(
            '/api/v1/employees',
            [
                'companyId'  => $data['company']->id,
                'personId'   => $data['person']->id,
                'workloadId' => $data['workload']->id,
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
                'companyId'      => $data['company']->id,
                'personId'       => $data['person']->id,
                'workloadId'     => $data['workload']->id,
                'kind'           => EmploymentTypeEnum::CLT->value,
                'isProbationary' => true,
            ]
        )->assertCreated();

        $person2 = Person::factory()->create();

        $this->postJson(
            '/api/v1/employees',
            [
                'companyId'      => $data['company']->id,
                'personId'       => $person2->id,
                'workloadId'     => $data['workload']->id,
                'kind'           => EmploymentTypeEnum::CLT->value,
                'isProbationary' => true,
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
        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $company,
        );

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        Employment::factory()->create([
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
        ]);

        $this->postJson(
            '/api/v1/employees',
            [
                'companyId'      => $employee->companyId,
                'personId'       => $employee->personId,
                'workloadId'     => $workload->id,
                'kind'           => EmploymentTypeEnum::CLT->value,
                'isProbationary' => true,
            ]
        )->assertConflict();
    }

    public function testPermiteContratarMesmaPersonComVinculoNaoCltAtivo(): void
    {
        $company  = Company::factory()->create();
        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $company,
        );

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        Employment::factory()->create([
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
            'kind'       => EmploymentTypeEnum::FREELANCER->value,
        ]);

        $workload2 = Workload::factory()->create(['companyId' => $company->id]);

        $this->postJson('/api/v1/employees', [
            'companyId'      => $employee->companyId,
            'personId'       => $employee->personId,
            'workloadId'     => $workload2->id,
            'kind'           => EmploymentTypeEnum::CLT->value,
            'isProbationary' => true,
        ])->assertCreated();
    }

    public function testPermiteRecontratacao(): void
    {
        $company  = Company::factory()->create();
        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $this->autenticarComPermissao(
            'job.employees.create',
            company: $company,
        );

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        Employment::factory()->left()->create([
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
        ]);

        $this->postJson(
            '/api/v1/employees',
            [
                'companyId'      => $employee->companyId,
                'personId'       => $employee->personId,
                'workloadId'     => $workload->id,
                'kind'           => EmploymentTypeEnum::CLT->value,
                'isProbationary' => true,
            ]
        )->assertCreated();

        // mesmo registro de funcionário, novo vínculo
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

    public function testOwnerPodeAtualizarFuncionario(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('owner', company: $company);

        ['employee' => $employee] = $this->criarFuncionarioComVinculo($company);

        $workload = Workload::factory()->create(['companyId' => $employee->companyId]);

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status'     => 'hired',
                'workloadId' => $workload->id,
                'kind'       => EmploymentTypeEnum::CLT->value,
            ]
        )->assertOk();

        $this->assertDatabaseHas('job.employments', [
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
            'status'     => 'hired',
        ]);
    }

    public function testOwnerPodeAtualizarTipoDeContratacao(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('owner', company: $company);

        ['employee' => $employee] = $this->criarFuncionarioComVinculo($company);

        $workload = Workload::factory()->create(['companyId' => $employee->companyId]);

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status'     => 'hired',
                'workloadId' => $workload->id,
                'kind'       => EmploymentTypeEnum::FREELANCER->value,
            ]
        )->assertOk();

        $this->assertDatabaseHas('job.employments', [
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
            'kind'       => 'freelancer',
        ]);
    }

    public function testRhPodeAtualizarOutroFuncionario(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('humanResource', company: $company);

        ['employee' => $employee] = $this->criarFuncionarioComVinculo($company);

        $workload = Workload::factory()->create(['companyId' => $employee->companyId]);

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status'     => 'hired',
                'workloadId' => $workload->id,
                'kind'       => EmploymentTypeEnum::CLT->value,
            ]
        )->assertOk();
    }

    public function testRhNaoPodeEditarASiMesmo(): void
    {
        $company = Company::factory()->create();
        $person  = Person::factory()->create();

        $this->autenticarComRole('humanResource', company: $company, person: $person);

        $employee = Employee::factory()->create([
            'companyId' => $company->id,
            'personId'  => $person->id,
        ]);

        $workload = Workload::factory()->create(['companyId' => $employee->companyId]);

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status'     => 'hired',
                'workloadId' => $workload->id,
            ]
        )->assertForbidden();
    }

    public function testFuncionarioComumNaoPodeAtualizar(): void
    {
        $company = Company::factory()->create();

        $this->autenticarSemPermissao(company: $company);

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        $workload = Workload::factory()->create(['companyId' => $employee->companyId]);

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status'     => 'hired',
                'workloadId' => $workload->id,
            ]
        )->assertForbidden();
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

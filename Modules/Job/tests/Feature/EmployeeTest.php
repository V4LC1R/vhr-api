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
                'companyId'  => $data['company']->id,
                'personId'   => $data['person']->id,
                'workloadId' => $data['workload']->id,
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
                'companyId'  => $data['company']->id,
                'personId'   => $data['person']->id,
                'workloadId' => $data['workload']->id,
            ]
        )->assertCreated();

        $person2 = Person::factory()->create();

        $this->postJson(
            '/api/v1/employees',
            [
                'companyId'  => $data['company']->id,
                'personId'   => $person2->id,
                'workloadId' => $data['workload']->id,
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
                'companyId'  => $employee->companyId,
                'personId'   => $employee->personId,
                'workloadId' => $workload->id,
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
            'companyId'  => $employee->companyId,
            'personId'   => $employee->personId,
            'workloadId' => $workload2->id,
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
                'companyId'  => $employee->companyId,
                'personId'   => $employee->personId,
                'workloadId' => $workload->id,
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
        $this->autenticarComPermissao('job.employees.view');

        $employee = Employee::factory()->create();

        $this->getJson("/api/v1/employees/{$employee->id}")->assertOk();
    }

    public function testUsuarioComPermissaoPodeListarFuncionarios(): void
    {
        $this->autenticarComPermissao('job.employees.view');

        Employee::factory()->count(3)->create();

        $this->getJson('/api/v1/employees')->assertOk();
    }

    public function testOwnerPodeAtualizarFuncionario(): void
    {
        $this->autenticarComRole('owner');

        ['employee' => $employee] = $this->criarFuncionarioComVinculo();

        $workload = Workload::factory()->create(['companyId' => $employee->companyId]);

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status'     => 'hired',
                'workloadId' => $workload->id,
            ]
        )->assertOk();

        $this->assertDatabaseHas('job.employments', [
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
            'status'     => 'hired',
        ]);
    }

    public function testRhPodeAtualizarOutroFuncionario(): void
    {
        $this->autenticarComRole('humanResource');

        ['employee' => $employee] = $this->criarFuncionarioComVinculo();

        $workload = Workload::factory()->create(['companyId' => $employee->companyId]);

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status'     => 'hired',
                'workloadId' => $workload->id,
            ]
        )->assertOk();
    }

    public function testRhNaoPodeEditarASiMesmo(): void
    {
        $person = Person::factory()->create();

        $this->autenticarComRole('humanResource', person: $person);

        $employee = Employee::factory()->create(['personId' => $person->id]);

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
        $this->autenticarSemPermissao();

        $employee = Employee::factory()->create();

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
        $this->autenticarComRole('owner');

        ['employee' => $employee] = $this->criarFuncionarioComVinculo();

        $this->patchJson("/api/v1/employees/{$employee->id}/dismiss")->assertOk();

        $latestEmployment = $employee->employments()->latest()->first();

        $this->assertEquals('left', $latestEmployment->status->value);
        $this->assertNotNull($latestEmployment->left_at);
    }

    public function testRhPodeDemitirOutroFuncionario(): void
    {
        $this->autenticarComRole('humanResource');

        ['employee' => $employee] = $this->criarFuncionarioComVinculo();

        $this->patchJson("/api/v1/employees/{$employee->id}/dismiss")->assertOk();
    }

    public function testRhNaoPodeDemitirASiMesmo(): void
    {
        $person = Person::factory()->create();

        $this->autenticarComRole('humanResource', person: $person);

        $employee = Employee::factory()->create(['personId' => $person->id]);

        $this->patchJson("/api/v1/employees/{$employee->id}/dismiss")->assertForbidden();
    }

    public function testFuncionarioComumNaoPodeDemitir(): void
    {
        $this->autenticarSemPermissao();

        $employee = Employee::factory()->create();

        $this->patchJson("/api/v1/employees/{$employee->id}/dismiss")->assertForbidden();
    }

    public function testOwnerPodeExcluirFuncionario(): void
    {
        $this->autenticarComRole('owner');

        $employee = Employee::factory()->create();

        $this->deleteJson("/api/v1/employees/{$employee->id}")->assertNoContent();

        $this->assertDatabaseMissing('job.employees', ['id' => $employee->id]);
        $this->assertDatabaseMissing('job.employments', ['employeeId' => $employee->id]);
    }

    public function testRhPodeExcluirOutroFuncionario(): void
    {
        $this->autenticarComRole('humanResource');

        $employee = Employee::factory()->create();

        $this->deleteJson("/api/v1/employees/{$employee->id}")->assertNoContent();
    }

    public function testRhNaoPodeExcluirASiMesmo(): void
    {
        $person = Person::factory()->create();

        $this->autenticarComRole('humanResource', person: $person);

        $employee = Employee::factory()->create(['personId' => $person->id]);

        $this->deleteJson("/api/v1/employees/{$employee->id}")->assertForbidden();
    }

    public function testFuncionarioComumNaoPodeExcluir(): void
    {
        $this->autenticarSemPermissao();

        $employee = Employee::factory()->create();

        $this->deleteJson("/api/v1/employees/{$employee->id}")->assertForbidden();
    }
}

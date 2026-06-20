<?php

namespace Modules\Job\Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Core\Models\User;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Workload;
use Tests\DBTestCase;

class EmployeeTest extends DBTestCase
{
    protected bool $seed = true;

    protected function autenticarComPermissao(
        string $permission
    ): User {
        $user = User::factory()->create();

        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        return $user;
    }

    protected function autenticarComRole(
        string $role,
        ?Person $person = null
    ): User {

        $person = $person ?? Person::factory()->create();

        $user = User::factory()->create([
            'personId' => $person?->id,
        ]);

        $user->assignRole($role);

        Sanctum::actingAs($user);

        return $user;
    }

    protected function autenticarSemPermissao(): User
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        return $user;
    }

    private function criarDadosFuncionario(): array
    {
        $company = Company::factory()->create();

        $person = Person::factory()->create();

        $workload = Workload::factory()->create([
            'companyId' => $company->id,
        ]);

        return compact(
            'company',
            'person',
            'workload'
        );
    }

    public function testUsuarioComPermissaoPodeContratarFuncionario(): void
    {
        $this->autenticarComPermissao(
            'job.employees.create'
        );

        $data = $this->criarDadosFuncionario();

        $response = $this->postJson(
            '/api/v1/employees',
            [
                'companyId' => $data['company']->id,
                'personId' => $data['person']->id,
                'workloadId' => $data['workload']->id,
            ]
        );

        $response->assertCreated();

        $this->assertDatabaseHas(
            'job.employees',
            [
                'companyId' => $data['company']->id,
                'personId' => $data['person']->id,
                'status' => 'experience',
            ]
        );
    }

    public function testUsuarioSemPermissaoNaoPodeContratarFuncionario(): void
    {
        $this->autenticarSemPermissao();

        $data = $this->criarDadosFuncionario();

        $this->postJson(
            '/api/v1/employees',
            [
                'companyId' => $data['company']->id,
                'personId' => $data['person']->id,
                'workloadId' => $data['workload']->id,
            ]
        )->assertForbidden();
    }

    public function testGeraMatriculaSequencialPorEmpresa(): void
    {
        $this->autenticarComPermissao(
            'job.employees.create'
        );

        $data = $this->criarDadosFuncionario();

        $this->postJson(
            '/api/v1/employees',
            [
                'companyId' => $data['company']->id,
                'personId' => $data['person']->id,
                'workloadId' => $data['workload']->id,
            ]
        )->assertCreated();

        $person2 = Person::factory()->create();

        $this->postJson(
            '/api/v1/employees',
            [
                'companyId' => $data['company']->id,
                'personId' => $person2->id,
                'workloadId' => $data['workload']->id,
            ]
        )->assertCreated();

        $registerNumbers = Employee::query()
            ->where('companyId', $data['company']->id)
            ->orderBy('registerNumber')
            ->pluck('registerNumber')
            ->values()
            ->all();

        $this->assertEquals(
            [1, 2],
            $registerNumbers
        );
    }

    public function testNaoPermiteVinculoAtivoDuplicado(): void
    {
        $this->autenticarComPermissao(
            'job.employees.create'
        );

        $employee = Employee::factory()->create([
            'status' => 'hired',
        ]);

        $this->postJson(
            '/api/v1/employees',
            [
                'companyId' => $employee->companyId,
                'personId' => $employee->personId,
                'workloadId' => $employee->workloadId,
            ]
        )->assertConflict();
    }

    public function testPermiteRecontratacao(): void
    {
        $this->autenticarComPermissao(
            'job.employees.create'
        );

        $employee = Employee::factory()->create([
            'status' => 'out',
            'left_at' => now(),
        ]);

        $this->postJson(
            '/api/v1/employees',
            [
                'companyId' => $employee->companyId,
                'personId' => $employee->personId,
                'workloadId' => $employee->workloadId,
            ]
        )->assertCreated();

        $this->assertEquals(
            2,
            Employee::query()
                ->where('companyId', $employee->companyId)
                ->where('personId', $employee->personId)
                ->count()
        );
    }

    public function testUsuarioComPermissaoPodeVisualizarFuncionario(): void
    {
        $this->autenticarComPermissao(
            'job.employees.view'
        );

        $employee = Employee::factory()->create();

        $this->getJson(
            "/api/v1/employees/{$employee->id}"
        )->assertOk();
    }

    public function testUsuarioComPermissaoPodeListarFuncionarios(): void
    {
        $this->autenticarComPermissao(
            'job.employees.view'
        );

        Employee::factory()
            ->count(3)
            ->create();

        $this->getJson(
            '/api/v1/employees'
        )->assertOk();
    }

    public function testOwnerPodeAtualizarFuncionario(): void
    {
        $this->autenticarComRole(
            'owner'
        );

        $employee = Employee::factory()->create();

        $workload = Workload::factory()->create([
            'companyId' => $employee->companyId,
        ]);

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status' => $employee->status,
                'role' => $employee->role,
                'workloadId' => $workload->id,
            ]
        )->assertOk();

        $this->assertDatabaseHas(
            'job.employees',
            [
                'id' => $employee->id,
                'workloadId' => $workload->id,
            ]
        );
    }

    public function testRhPodeAtualizarOutroFuncionario(): void
    {
        $this->autenticarComRole(
            'humanResource'
        );

        $employee = Employee::factory()->create();

        $workload = Workload::factory()->create([
            'companyId' => $employee->companyId,
        ]);

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status' => $employee->status,
                'role' => $employee->role,
                'workloadId' => $workload->id,
            ]
        )->assertOk();
    }

    public function testRhNaoPodeEditarASiMesmo(): void
    {
        $person = Person::factory()->create();

        $this->autenticarComRole(
            'humanResource',
            $person
        );

        $employee = Employee::factory()->create([
            'personId' => $person->id,
        ]);

        $workload = Workload::factory()->create([
            'companyId' => $employee->companyId,
        ]);

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status' => $employee->status,
                'role' => $employee->role,
                'workloadId' => $workload->id,
            ]
        )->assertForbidden();
    }

    public function testFuncionarioComumNaoPodeAtualizar(): void
    {
        $this->autenticarSemPermissao();

        $employee = Employee::factory()->create();

        $this->putJson(
            "/api/v1/employees/{$employee->id}",
            [
                'status' => $employee->status,
                'role' => $employee->role,
                'workloadId' => $employee->workloadId,
            ]
        )->assertForbidden();
    }

    public function testOwnerPodeDemitirFuncionario(): void
    {
        $this->autenticarComRole(
            'owner'
        );

        $employee = Employee::factory()->create([
            'status' => 'hired',
            'left_at' => null,
        ]);

        $this->patchJson(
            "/api/v1/employees/{$employee->id}/dismiss"
        )->assertOk();

        $employee->refresh();

        $this->assertEquals(
            'out',
            $employee->status
        );

        $this->assertNotNull(
            $employee->left_at
        );
    }

    public function testRhPodeDemitirOutroFuncionario(): void
    {
        $this->autenticarComRole(
            'humanResource'
        );

        $employee = Employee::factory()->create([
            'status' => 'hired',
        ]);

        $this->patchJson(
            "/api/v1/employees/{$employee->id}/dismiss"
        )->assertOk();
    }

    public function testRhNaoPodeDemitirASiMesmo(): void
    {
        $person = Person::factory()->create();

        $this->autenticarComRole(
            'humanResource',
            $person
        );

        $employee = Employee::factory()->create([
            'personId' => $person->id,
        ]);

        $this->patchJson(
            "/api/v1/employees/{$employee->id}/dismiss"
        )->assertForbidden();
    }

    public function testFuncionarioComumNaoPodeDemitir(): void
    {
        $this->autenticarSemPermissao();

        $employee = Employee::factory()->create();

        $this->patchJson(
            "/api/v1/employees/{$employee->id}/dismiss"
        )->assertForbidden();
    }

    public function testOwnerPodeExcluirFuncionario(): void
    {
        $this->autenticarComRole(
            'owner'
        );

        $employee = Employee::factory()->create();

        $this->deleteJson(
            "/api/v1/employees/{$employee->id}"
        )->assertNoContent();

        $this->assertDatabaseMissing(
            'job.employees',
            [
                'id' => $employee->id,
            ]
        );
    }

    public function testRhPodeExcluirOutroFuncionario(): void
    {
        $this->autenticarComRole(
            'humanResource'
        );

        $employee = Employee::factory()->create();

        $this->deleteJson(
            "/api/v1/employees/{$employee->id}"
        )->assertNoContent();
    }

    public function testRhNaoPodeExcluirASiMesmo(): void
    {
        $person = Person::factory()->create();

        $this->autenticarComRole(
            'humanResource',
            $person
        );

        $employee = Employee::factory()->create([
            'personId' => $person->id,
        ]);

        $this->deleteJson(
            "/api/v1/employees/{$employee->id}"
        )->assertForbidden();
    }

    public function testFuncionarioComumNaoPodeExcluir(): void
    {
        $this->autenticarSemPermissao();

        $employee = Employee::factory()->create();

        $this->deleteJson(
            "/api/v1/employees/{$employee->id}"
        )->assertForbidden();
    }
}

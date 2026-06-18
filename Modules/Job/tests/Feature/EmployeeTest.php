<?php

namespace Modules\Job\Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Core\Models\User;
use Modules\Job\Models\Employee;
use Modules\Job\Models\PersonCompany;
use Modules\Job\Models\Workload;
use Tests\DBTestCase;

class EmployeeTest extends DBTestCase
{
    protected bool $seed = true;

    protected function autenticarComPermissao(string $permission): User
    {
        $user = User::factory()->create();

        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        return $user;
    }

    protected function autenticarSemPermissao(): User
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        return $user;
    }

    // ==========================================
    // 1. CONTRATAÇÃO (CREATE)
    // ==========================================

    public function testUsuarioComPermissaoPodeContratarFuncionario(): void
    {
        $this->autenticarComPermissao('job.employees.create');

        $company = Company::factory()->create();

        $person = Person::factory()->create();

        $workload = Workload::factory()->create([
            'companyId' => $company->id,
        ]);

        $payload = [
            'companyId' => $company->id,
            'personId' => $person->id,
            'workloadId' => $workload->id,
            'registerNumber' => 123,
            'register_at' => now()->toISOString(),
        ];

        $response = $this->postJson(
            '/api/v1/job/employees',
            $payload
        );

        $response->assertStatus(201);

        $this->assertDatabaseHas('job.person_companies', [
            'companyId' => $company->id,
            'personId' => $person->id,
        ]);

        $this->assertDatabaseHas('job.employees', [
            'registerNumber' => 123,
        ]);
    }

    public function testUsuarioSemPermissaoNaoPodeContratarFuncionario(): void
    {
        $this->autenticarSemPermissao();

        $company = Company::factory()->create();

        $person = Person::factory()->create();

        $workload = Workload::factory()->create([
            'companyId' => $company->id,
        ]);

        $payload = [
            'companyId' => $company->id,
            'personId' => $person->id,
            'workloadId' => $workload->id,
            'registerNumber' => 123,
            'register_at' => now()->toISOString(),
        ];

        $response = $this->postJson(
            '/api/v1/job/employees',
            $payload
        );

        $response->assertStatus(403);

        $this->assertDatabaseCount('job.person_companies', 0);
        $this->assertDatabaseCount('job.employees', 0);
    }

    public function testNaoPermiteContratacaoDuplicada(): void
    {
        $this->autenticarComPermissao('job.employees.create');

        $company = Company::factory()->create();

        $person = Person::factory()->create();

        $workload = Workload::factory()->create([
            'companyId' => $company->id,
        ]);

        $payload = [
            'companyId' => $company->id,
            'personId' => $person->id,
            'workloadId' => $workload->id,
            'registerNumber' => 123,
            'register_at' => now()->toISOString(),
        ];

        $this->postJson('/api/v1/job/employees', $payload)
            ->assertStatus(201);

        $response = $this->postJson('/api/v1/job/employees', $payload);

        $response->assertStatus(422);

        $this->assertDatabaseCount('job.person_companies', 1);
        $this->assertDatabaseCount('job.employees', 1);
    }

    // ==========================================
    // 2. VISUALIZAÇÃO (VIEW)
    // ==========================================

    public function testUsuarioComPermissaoPodeVisualizarFuncionario(): void
    {
        $this->autenticarComPermissao('job.employees.view');

        $employee = Employee::factory()->create();

        $response = $this->getJson(
            "/api/v1/job/employees/{$employee->id}"
        );

        $response->assertStatus(200);
    }

    public function testUsuarioSemPermissaoNaoPodeVisualizarFuncionario(): void
    {
        $this->autenticarSemPermissao();

        $employee = Employee::factory()->create();

        $response = $this->getJson(
            "/api/v1/job/employees/{$employee->id}"
        );

        $response->assertStatus(403);
    }

    public function testUsuarioComPermissaoPodeListarFuncionarios(): void
    {
        $this->autenticarComPermissao('job.employees.view');

        Employee::factory()->count(3)->create();

        $response = $this->getJson(
            '/api/v1/job/employees'
        );

        $response->assertStatus(200);
    }

    // ==========================================
    // 3. EDIÇÃO (UPDATE)
    // ==========================================

    public function testUsuarioComPermissaoPodeAtualizarFuncionario(): void
    {
        $this->autenticarComPermissao('job.employees.update');

        $employee = Employee::factory()->create([
            'registerNumber' => 100,
        ]);

        $payload = [
            'registerNumber' => 200,
        ];

        $response = $this->putJson(
            "/api/v1/job/employees/{$employee->id}",
            $payload
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('job.employees', [
            'id' => $employee->id,
            'registerNumber' => 200,
        ]);
    }

    public function testUsuarioSemPermissaoNaoPodeAtualizarFuncionario(): void
    {
        $this->autenticarSemPermissao();

        $employee = Employee::factory()->create([
            'registerNumber' => 100,
        ]);

        $payload = [
            'registerNumber' => 200,
        ];

        $response = $this->putJson(
            "/api/v1/job/employees/{$employee->id}",
            $payload
        );

        $response->assertStatus(403);

        $this->assertDatabaseHas('job.employees', [
            'id' => $employee->id,
            'registerNumber' => 100,
        ]);
    }

    // ==========================================
    // 4. DESLIGAMENTO (DELETE)
    // ==========================================

    public function testUsuarioComPermissaoPodeDesligarFuncionario(): void
    {
        $this->autenticarComPermissao('job.employees.delete');

        $personCompany = PersonCompany::factory()->create([
            'status' => 'hired',
        ]);

        $employee = Employee::factory()->create([
            'personCompanyId' => $personCompany->id,
        ]);

        $response = $this->deleteJson(
            "/api/v1/job/employees/{$employee->id}"
        );

        $response->assertStatus(204);

        $employee->refresh();
        $personCompany->refresh();

        $this->assertNotNull($employee->left_at);

        $this->assertEquals(
            'out',
            $personCompany->status
        );
    }

    public function testUsuarioSemPermissaoNaoPodeDesligarFuncionario(): void
    {
        $this->autenticarSemPermissao();

        $employee = Employee::factory()->create();

        $response = $this->deleteJson(
            "/api/v1/job/employees/{$employee->id}"
        );

        $response->assertStatus(403);
    }
}

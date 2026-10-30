<?php

namespace Modules\Job\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
use Modules\Job\Models\Workload;
use Tests\DBTestCase;

class EmploymentTest extends DBTestCase
{
    protected bool $seed = true;

    private function criarFuncionarioComVinculo(
        ?Company $company = null,
        ?EmploymentTypeEnum $kind = null,
    ): array {
        $employee = Employee::factory()->create(
            $company ? ['companyId' => $company->id] : []
        );

        $workload = Workload::factory()->create([
            'companyId' => $employee->companyId,
        ]);

        $employment = Employment::factory()->create([
            'companyId' => $employee->companyId,
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
            'kind' => ($kind ?? EmploymentTypeEnum::CLT)->value,
        ]);

        return compact('employee', 'workload', 'employment');
    }

    // ==========================================
    // CRIAÇÃO
    // ==========================================

    public function testCreateNewEmploymentToEmployee(): void
    {
        $company = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
            kind: EmploymentTypeEnum::CLT,
        );

        $this->autenticarComPermissao(
            'job.employment.create',
            company: $company,
        );

        $response = $this->postJson('/api/v1/employment', [
            'workloadId' => $data['workload']->id,
            'kind' => EmploymentTypeEnum::DAYLI->value,
            'employeeId' => $data['employee']->id,
            'isProbationary' => true,
        ]);

        $response->assertCreated();
    }

    public function testNaoDeveCriarEmploymentComWorkloadDeOutraEmpresa(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
        );

        $otherWorkload = Workload::factory()->create([
            'companyId' => $otherCompany->id,
        ]);

        $this->autenticarComPermissao(
            'job.employment.create',
            company: $company,
        );

        $response = $this->postJson('/api/v1/employment', [
            'workloadId' => $otherWorkload->id,
            'kind' => EmploymentTypeEnum::DAYLI->value,
            'employeeId' => $data['employee']->id,
            'isProbationary' => false,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['workloadId']);
    }

    public function testNaoDeveCriarEmploymentComEmployeeDeOutraEmpresa(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
        );

        $otherEmployee = Employee::factory()->create([
            'companyId' => $otherCompany->id,
        ]);

        $this->autenticarComPermissao(
            'job.employment.create',
            company: $company,
        );

        $response = $this->postJson('/api/v1/employment', [
            'workloadId' => $data['workload']->id,
            'kind' => EmploymentTypeEnum::DAYLI->value,
            'employeeId' => $otherEmployee->id,
            'isProbationary' => false,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['employeeId']);
    }

    public function testNaoDeveCriarEmploymentComTipoInvalido(): void
    {
        $company = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
        );

        $this->autenticarComPermissao(
            'job.employment.create',
            company: $company,
        );

        $response = $this->postJson('/api/v1/employment', [
            'workloadId' => $data['workload']->id,
            'kind' => 'INVALID',
            'employeeId' => $data['employee']->id,
            'isProbationary' => false,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['kind']);
    }

    public function testDeveExigirIsProbationaryParaEmploymentClt(): void
    {
        $company = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
            kind: EmploymentTypeEnum::DAYLI
        );

        $this->autenticarComPermissao(
            'job.employment.create',
            company: $company,
        );

        $response = $this->postJson('/api/v1/employment', [
            'workloadId' => $data['workload']->id,
            'employeeId' => $data['employee']->id,
            'kind' => EmploymentTypeEnum::CLT->value,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['isProbationary']);
    }

    public function testNaoDeveExigirIsProbationaryParaEmploymentDayli(): void
    {
        $company = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
        );

        $this->autenticarComPermissao(
            'job.employment.create',
            company: $company,
        );

        $response = $this->postJson('/api/v1/employment', [
            'workloadId' => $data['workload']->id,
            'kind' => EmploymentTypeEnum::DAYLI->value,
            'employeeId' => $data['employee']->id,
        ]);

        $response->assertCreated();
    }

    // ==========================================
    // EDIÇÃO
    // ==========================================

    public function testEditarEmploymentSemAlterarTipo(): void
    {
        $company = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
            kind: EmploymentTypeEnum::CLT,
        );

        $this->autenticarComRole(
            'humanResource',
            company: $company,
        );

        $response = $this->putJson(
            "/api/v1/employment/{$data['employment']->id}",
            [
                'workloadId' => $data['workload']->id,
                'kind' => EmploymentTypeEnum::CLT->value,
                'employeeId' => $data['employee']->id,
                'isProbationary' => true,
            ]
        );

        $response->assertOk();
    }

    public function testNaoDevePermitirAlterarEmploymentParaCltSeJaForClt(): void
    {
        $company = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
            kind: EmploymentTypeEnum::CLT,
        );

        $employmentDayli = Employment::factory()->create([
            'companyId' => $company->id,
            'workloadId' => $data['workload']->id,
            'kind' => EmploymentTypeEnum::DAYLI->value,
            'employeeId' => $data['employee']->id,
        ]);

        $this->autenticarComRole(
            'humanResource',
            company: $company,
        );

        $response = $this->putJson(
            "/api/v1/employment/{$employmentDayli->id}",
            [
                'workloadId' => $data['workload']->id,
                'kind' => EmploymentTypeEnum::CLT->value,
                'employeeId' => $data['employee']->id,
                'isProbationary' => true,
            ]
        );

        $response->assertConflict();
    }

    public function testNaoDeveAtualizarEmploymentComWorkloadDeOutraEmpresa(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
            kind: EmploymentTypeEnum::DAYLI,
        );

        $otherWorkload = Workload::factory()->create([
            'companyId' => $otherCompany->id,
        ]);

        $this->autenticarComRole(
            'humanResource',
            company: $company,
        );

        $response = $this->putJson(
            "/api/v1/employment/{$data['employment']->id}",
            [
                'workloadId' => $otherWorkload->id,
                'kind' => EmploymentTypeEnum::DAYLI->value,
                'isProbationary' => false,
            ]
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['workloadId']);
    }

    public function testNaoDeveAtualizarEmploymentComTipoInvalido(): void
    {
        $company = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
            kind: EmploymentTypeEnum::DAYLI,
        );

        $this->autenticarComRole(
            'humanResource',
            company: $company,
        );

        $response = $this->putJson(
            "/api/v1/employment/{$data['employment']->id}",
            [
                'workloadId' => $data['workload']->id,
                'kind' => 'INVALID',
                'isProbationary' => false,
            ]
        );

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['kind']);
    }

    public function testNaoDevePermitirEditarEmploymentDeOutraEmpresa(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $data = $this->criarFuncionarioComVinculo(
            company: $company,
        );

        $this->autenticarComRole(
            'humanResource',
            company: $otherCompany,
        );
        $response = $this->putJson(
            "/api/v1/employment/{$data['employment']->id}",
            [
                'workloadId' => $data['workload']->id,
                'kind' => EmploymentTypeEnum::DAYLI->value,
                'isProbationary' => false,
            ]
        );

        $response->assertNotFound();
    }
}

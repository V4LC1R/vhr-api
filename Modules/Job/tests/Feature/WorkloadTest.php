<?php

namespace Modules\Job\Tests\Feature;

use Modules\Core\Models\Company;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
use Modules\Job\Models\Workload;
use Tests\DBTestCase;

class WorkloadTest extends DBTestCase
{
    protected bool $seed = true;

    /**
     * Helper para gerar os dados válidos de uma jornada de trabalho.
     */
    private function dadosWorkload(string $companyId): array
    {
        return [
            'companyId'         => $companyId,
            'description'       => 'Jornada Padrão 44h',
            'monthlyHours'     => 220,
            'weeklyHours'      => 44,
            'entryTime'        => '08:00:00',
            'leftTime'         => '18:00:00',
            'intervalStartAt' => '12:00:00',
            'intervalEndAt'   => '13:00:00',
        ];
    }

    // ==========================================
    // CRIAÇÃO (POST)
    // ==========================================

    public function testUsuarioComPermissaoPodeCriarWorkload(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComPermissao(
            'job.workloads.create',
            company: $company
        );

        $payload = $this->dadosWorkload($company->id);

        $response = $this->postJson('/api/v1/workloads', $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('job.workloads', [
            'companyId'   => $company->id,
            'description' => 'Jornada Padrão 44h',
        ]);
    }

    public function testUsuarioSemPermissaoNaoPodeCriarWorkload(): void
    {
        $this->autenticarSemPermissao();

        $company = Company::factory()->create();
        $payload = $this->dadosWorkload($company->id);

        $this->postJson('/api/v1/workloads', $payload)->assertForbidden();
    }

    // ==========================================
    // LEITURA / LISTAGEM (GET)
    // ==========================================

    public function testUsuarioComPermissaoPodeVisualizarWorkload(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComPermissao('job.workloads.view', company: $company);

        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $response = $this->getJson("/api/v1/workloads/{$workload->id}")
            ->assertOk();

        $response->assertJsonPath('id', $workload->id);
    }

    public function testUsuarioComPermissaoPodeListarWorkloads(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComPermissao('job.workloads.view', company: $company);

        Workload::factory()->count(3)->create(['companyId' => $company->id]);

        $this->getJson('/api/v1/workloads')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    // ==========================================
    // ATUALIZAÇÃO (PUT)
    // ==========================================

    public function testOwnerPodeAtualizarWorkload(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('owner', company: $company);

        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $payload = array_merge(
            $this->dadosWorkload($company->id),
            ['description' => 'Jornada Reduzida 30h', 'weeklyHours' => 30]
        );

        $this->putJson("/api/v1/workloads/{$workload->id}", $payload)->assertOk();

        $this->assertDatabaseHas('job.workloads', [
            'id'           => $workload->id,
            'description'  => 'Jornada Reduzida 30h',
            'weeklyHours' => 30,
        ]);
    }

    public function testRhPodeAtualizarWorkload(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('humanResource', company: $company);

        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $payload = array_merge($this->dadosWorkload($company->id), [
            'description' => 'Alterado pelo RH'
        ]);

        $this->putJson("/api/v1/workloads/{$workload->id}", $payload)->assertOk();

        $this->assertDatabaseHas('job.workloads', [
            'id'          => $workload->id,
            'description' => 'Alterado pelo RH',
        ]);
    }

    public function testFuncionarioComumNaoPodeAtualizarWorkload(): void
    {
        $company = Company::factory()->create();

        $this->autenticarSemPermissao(company: $company);

        $workload = Workload::factory()->create(['companyId' => $company->id]);
        $payload = $this->dadosWorkload($workload->companyId);

        $this->putJson("/api/v1/workloads/{$workload->id}", $payload)->assertForbidden();
    }

    // ==========================================
    // EXCLUSÃO (DELETE)
    // ==========================================

    public function testOwnerPodeExcluirWorkload(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('owner', company: $company);

        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $this->deleteJson("/api/v1/workloads/{$workload->id}")->assertNoContent();

        $this->assertSoftDeleted('job.workloads', ['id' => $workload->id]);
    }

    public function testRhPodeExcluirWorkload(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('humanResource', company: $company);

        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $this->deleteJson("/api/v1/workloads/{$workload->id}")->assertNoContent();

        $this->assertSoftDeleted('job.workloads', ['id' => $workload->id]);
    }

    public function testFuncionarioComumNaoPodeExcluirWorkload(): void
    {
        $company = Company::factory()->create();

        $this->autenticarSemPermissao(company: $company);

        $workload = Workload::factory()->create(['companyId' => $company->id]);

        $this->deleteJson("/api/v1/workloads/{$workload->id}")->assertForbidden();
    }

    public function testNaoPodeExcluirWorkloadComVinculoAtivo(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('owner', company: $company);

        $workload = Workload::factory()->create(['companyId' => $company->id]);
        $employee = Employee::factory()->create(['companyId' => $company->id]);

        $employment = Employment::factory()->hired()->create([
            'employeeId'  => $employee->id,
            'workloadId' => $workload->id,
        ]);

        $this->deleteJson("/api/v1/workloads/{$workload->id}")
            ->assertConflict()
            ->assertJsonPath(
                'message',
                'Esta jornada está vinculada a colaboradores ativos e não pode ser excluída.'
            );

        $this->assertNotSoftDeleted('job.workloads', ['id' => $workload->id]);
        $this->assertDatabaseHas('job.employments', ['id' => $employment->id]);
    }

    public function testPodeExcluirWorkloadComVinculoEncerrado(): void
    {
        $company = Company::factory()->create();

        $this->autenticarComRole('owner', company: $company);

        $workload = Workload::factory()->create(['companyId' => $company->id]);
        $employee = Employee::factory()->create(['companyId' => $company->id]);

        $employment = Employment::factory()->left()->create([
            'employeeId'  => $employee->id,
            'workloadId' => $workload->id,
        ]);

        // Vínculo encerrado não bloqueia: soft delete some da listagem mas o
        // histórico continua apontando pra jornada.
        $this->deleteJson("/api/v1/workloads/{$workload->id}")->assertNoContent();

        $this->assertSoftDeleted('job.workloads', ['id' => $workload->id]);
        $this->assertDatabaseHas('job.employments', [
            'id'          => $employment->id,
            'workloadId' => $workload->id,
        ]);

        $this->getJson('/api/v1/workloads')
            ->assertOk()
            ->assertJsonMissing(['id' => $workload->id]);
    }
}

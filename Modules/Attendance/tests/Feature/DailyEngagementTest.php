<?php

namespace Modules\Attendance\Tests\Feature;

use Modules\Attendance\Models\DailyEngagement;
use Modules\Core\Models\Company;
use Modules\Core\Models\Person;
use Modules\Core\Models\UserCompany;
use Modules\Job\Models\Employee;
use Tests\DBTestCase;

class DailyEngagementTest extends DBTestCase
{
    protected bool $seed = true;

    private function diaParaNovoFuncionario(Company $company): DailyEngagement
    {
        $employee = Employee::factory()->create(['companyId' => $company->id]);

        return DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $employee->id,
        ]);
    }

    // ==========================================
    // LISTAGEM / LEITURA
    // ==========================================

    public function testGestorPodeListarDias(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('humanResource', company: $company);

        $this->diaParaNovoFuncionario($company);
        $this->diaParaNovoFuncionario($company);
        $this->diaParaNovoFuncionario($company);

        $this->getJson('/api/v1/daily-engagements')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
            ])
            ->assertJsonCount(3, 'data');
    }

    public function testGestorPodeVisualizarDia(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('humanResource', company: $company);

        $day = $this->diaParaNovoFuncionario($company);

        $this->getJson("/api/v1/daily-engagements/{$day->id}")
            ->assertOk()
            ->assertJsonPath('id', $day->id);
    }

    public function testFuncionarioComumSoVeOsProprios(): void
    {
        $company = Company::factory()->create();
        $person  = Person::factory()->create();

        $this->autenticarComRole('employee', company: $company, person: $person);

        $proprio = Employee::factory()->create([
            'companyId' => $company->id,
            'personId'  => $person->id,
        ]);
        DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $proprio->id,
        ]);

        // dia de outro funcionário
        $this->diaParaNovoFuncionario($company);

        $this->getJson('/api/v1/daily-engagements')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ==========================================
    // EXCEÇÕES
    // ==========================================

    public function testGestorPodeMarcarFolga(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('humanResource', company: $company);

        $day = $this->diaParaNovoFuncionario($company);

        $this->patchJson("/api/v1/daily-engagements/{$day->id}/exception", [
            'type' => 'holiday',
        ])->assertOk();

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'id'               => $day->id,
            'type'             => 'holiday',
            'expected_minutes' => 0,
        ]);
    }

    // ==========================================
    // APROVAÇÃO
    // ==========================================

    public function testOwnerPodeAprovarDia(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', company: $company);

        $day = $this->diaParaNovoFuncionario($company);

        $this->postJson("/api/v1/daily-engagements/{$day->id}/approve")
            ->assertOk();

        $day->refresh();
        $this->assertEquals('approved', $day->status->value);
        $this->assertNotNull($day->approvedBy);
        $this->assertNotNull($day->approvedAt);
    }

    public function testRhNaoPodeAprovarDia(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('humanResource', company: $company);

        $day = $this->diaParaNovoFuncionario($company);

        $this->postJson("/api/v1/daily-engagements/{$day->id}/approve")
            ->assertForbidden();
    }

    public function testUsuarioSemPermissaoNaoPodeListar(): void
    {
        $this->autenticarSemPermissao();

        $this->getJson('/api/v1/daily-engagements')->assertForbidden();
    }

    // ==========================================
    // RASCUNHO (draft)
    // ==========================================

    public function testRascunhoSoVisivelParaQuemDraftou(): void
    {
        $company = Company::factory()->create();
        $user = $this->autenticarComRole('humanResource', company: $company);
        $minhaUc = UserCompany::query()
            ->where('userId', $user->id)
            ->where('companyId', $company->id)
            ->firstOrFail();
        $outraUc = UserCompany::factory()->create(['companyId' => $company->id]);

        // meu rascunho (visível)
        $meuEmp = Employee::factory()->create(['companyId' => $company->id]);
        DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $meuEmp->id,
            'status'     => 'draft',
            'draftedBy'  => $minhaUc->id,
        ]);

        // rascunho de outro (oculto)
        $outroEmp = Employee::factory()->create(['companyId' => $company->id]);
        DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $outroEmp->id,
            'status'     => 'draft',
            'draftedBy'  => $outraUc->id,
        ]);

        $this->getJson('/api/v1/daily-engagements')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function testGestorPodeEnviarRascunhoParaAprovacao(): void
    {
        $company = Company::factory()->create();
        $user = $this->autenticarComRole('humanResource', company: $company);
        $minhaUc = UserCompany::query()
            ->where('userId', $user->id)
            ->where('companyId', $company->id)
            ->firstOrFail();

        $employee = Employee::factory()->create(['companyId' => $company->id]);
        $day = DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $employee->id,
            'status'     => 'draft',
            'draftedBy'  => $minhaUc->id,
        ]);

        $this->postJson("/api/v1/daily-engagements/{$day->id}/submit")
            ->assertOk();

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'id'     => $day->id,
            'status' => 'pending',
        ]);
    }

    // ==========================================
    // CONTADOR (relatório de horas)
    // ==========================================

    public function testContadorSoVeDiasAprovados(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('accountant', company: $company);

        $aprovadoEmp = Employee::factory()->create(['companyId' => $company->id]);
        DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $aprovadoEmp->id,
            'status'     => 'approved',
        ]);

        $pendenteEmp = Employee::factory()->create(['companyId' => $company->id]);
        DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $pendenteEmp->id,
            'status'     => 'pending',
        ]);

        $this->getJson('/api/v1/daily-engagements')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}

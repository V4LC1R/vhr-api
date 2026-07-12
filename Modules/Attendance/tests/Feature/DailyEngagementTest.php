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
            'note' => 'Feriado municipal',
        ])->assertOk();

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'id'               => $day->id,
            'type'             => 'holiday',
            'expectedMinutes' => 0,
        ]);
    }

    public function testExcecaoSemObservacaoEhRejeitada(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('humanResource', company: $company);

        $day = $this->diaParaNovoFuncionario($company);

        $this->patchJson("/api/v1/daily-engagements/{$day->id}/exception", [
            'type' => 'holiday',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('note');
    }

    // ==========================================
    // EXCEÇÃO POR FUNCIONÁRIO+DATA (STORE)
    // ==========================================

    public function testGestorPodeMarcarFaltaEmDiaSemLancamento(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('humanResource', company: $company);

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        $this->postJson('/api/v1/daily-engagements', [
            'employeeId' => $employee->id,
            'date'       => '2026-07-06',
            'type'       => 'absence',
            'note'       => 'Não compareceu',
        ])->assertCreated();

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'employeeId' => $employee->id,
            'type'       => 'absence',
            'status'     => 'draft',
            'note'       => 'Não compareceu',
        ]);
    }

    public function testExcecaoPorDataAtualizaDiaExistente(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('humanResource', company: $company);

        $employee = Employee::factory()->create(['companyId' => $company->id]);
        $day = DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $employee->id,
            'date'       => '2026-07-06',
            'status'     => 'pending',
        ]);

        $this->postJson('/api/v1/daily-engagements', [
            'employeeId' => $employee->id,
            'date'       => '2026-07-06',
            'type'       => 'day_off',
            'note'       => 'Folga combinada',
        ])->assertCreated();

        // Atualiza o dia existente (não duplica) e volta pra rascunho.
        $this->assertEquals(1, DailyEngagement::query()
            ->where('employeeId', $employee->id)
            ->whereDate('date', '2026-07-06')
            ->count());

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'id'     => $day->id,
            'type'   => 'day_off',
            'status' => 'draft',
        ]);
    }

    public function testFuncionarioComumNaoPodeLancarExcecao(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('employee', company: $company);

        $employee = Employee::factory()->create(['companyId' => $company->id]);

        $this->postJson('/api/v1/daily-engagements', [
            'employeeId' => $employee->id,
            'date'       => '2026-07-06',
            'type'       => 'absence',
            'note'       => 'Não compareceu',
        ])->assertForbidden();
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

    public function testAprovacaoExpoeNomeDoAprovador(): void
    {
        $company = Company::factory()->create();
        $person  = Person::factory()->create(['name' => 'Aprovador Teste']);
        $this->autenticarComRole('owner', company: $company, person: $person);

        $day = $this->diaParaNovoFuncionario($company);

        $this->postJson("/api/v1/daily-engagements/{$day->id}/approve")
            ->assertOk()
            ->assertJsonPath('approval.byName', 'Aprovador Teste');
    }

    // ==========================================
    // APROVAÇÃO / REJEIÇÃO EM LOTE
    // ==========================================

    public function testOwnerAprovaEmLote(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', company: $company);

        $pendente1 = $this->diaParaNovoFuncionario($company);
        $pendente2 = $this->diaParaNovoFuncionario($company);

        // Rascunho não pode ser aprovado — deve ser pulado.
        $rascunhoEmp = Employee::factory()->create(['companyId' => $company->id]);
        $rascunho = DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $rascunhoEmp->id,
            'status'     => 'draft',
        ]);

        $this->postJson('/api/v1/daily-engagements/approve-batch', [
            'ids' => [$pendente1->id, $pendente2->id, $rascunho->id],
        ])
            ->assertOk()
            ->assertJsonPath('approved', 2)
            ->assertJsonPath('skipped', 1);

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'id'     => $pendente1->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('attendance.daily_engagements', [
            'id'     => $pendente2->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('attendance.daily_engagements', [
            'id'     => $rascunho->id,
            'status' => 'draft',
        ]);
    }

    public function testLoteNaoAprovaDiaDeOutraEmpresa(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', company: $company);

        $outraEmpresa = Company::factory()->create();
        $diaDeFora = $this->diaParaNovoFuncionario($outraEmpresa);

        $this->postJson('/api/v1/daily-engagements/approve-batch', [
            'ids' => [$diaDeFora->id],
        ])
            ->assertOk()
            ->assertJsonPath('approved', 0)
            ->assertJsonPath('skipped', 1);

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'id'     => $diaDeFora->id,
            'status' => 'pending',
        ]);
    }

    public function testRhNaoPodeAprovarEmLote(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('humanResource', company: $company);

        $day = $this->diaParaNovoFuncionario($company);

        $this->postJson('/api/v1/daily-engagements/approve-batch', [
            'ids' => [$day->id],
        ])->assertForbidden();
    }

    public function testOwnerRejeitaEmLoteComMotivo(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', company: $company);

        $dia1 = $this->diaParaNovoFuncionario($company);
        $dia2 = $this->diaParaNovoFuncionario($company);

        $this->postJson('/api/v1/daily-engagements/reject-batch', [
            'ids'  => [$dia1->id, $dia2->id],
            'note' => 'Horários inconsistentes',
        ])
            ->assertOk()
            ->assertJsonPath('rejected', 2)
            ->assertJsonPath('skipped', 0);

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'id'     => $dia1->id,
            'status' => 'rejected',
            'note'   => 'Horários inconsistentes',
        ]);
        $this->assertDatabaseHas('attendance.daily_engagements', [
            'id'     => $dia2->id,
            'status' => 'rejected',
            'note'   => 'Horários inconsistentes',
        ]);
    }

    public function testRejeicaoEmLoteSemMotivoEhRejeitada(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', company: $company);

        $day = $this->diaParaNovoFuncionario($company);

        $this->postJson('/api/v1/daily-engagements/reject-batch', [
            'ids' => [$day->id],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('note');
    }

    public function testRejeicaoIndividualSemMotivoEhRejeitada(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', company: $company);

        $day = $this->diaParaNovoFuncionario($company);

        $this->postJson("/api/v1/daily-engagements/{$day->id}/reject")
            ->assertUnprocessable()
            ->assertJsonValidationErrors('note');
    }

    public function testEnviarDiaNaoRascunhoRetornaConflito(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('humanResource', company: $company);

        // Factory cria como pending — não é rascunho.
        $day = $this->diaParaNovoFuncionario($company);

        $this->postJson("/api/v1/daily-engagements/{$day->id}/submit")
            ->assertConflict();
    }

    public function testAprovarDiaNaoPendenteRetornaConflito(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', company: $company);

        $employee = Employee::factory()->create(['companyId' => $company->id]);
        $day = DailyEngagement::factory()->approved()->create([
            'companyId'  => $company->id,
            'employeeId' => $employee->id,
        ]);

        $this->postJson("/api/v1/daily-engagements/{$day->id}/approve")
            ->assertConflict();
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

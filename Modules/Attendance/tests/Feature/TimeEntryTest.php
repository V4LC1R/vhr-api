<?php

namespace Modules\Attendance\Tests\Feature;

use Modules\Attendance\Models\DailyEngagement;
use Modules\Attendance\Models\TimeEntry;
use Modules\Core\Models\Company;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
use Modules\Job\Models\Workload;
use Tests\DBTestCase;

class TimeEntryTest extends DBTestCase
{
    protected bool $seed = true;

    /**
     * Cria um funcionário com vínculo ativo e jornada (08:00–18:00, intervalo 12:00–13:00).
     */
    private function funcionarioComJornada(
        Company $company,
        ?string $kind = null
    ): Employee {
        $employee = Employee::factory()->create(['companyId' => $company->id]);
        $workload = Workload::factory()->create(['companyId' => $company->id]);

        Employment::factory()->create(array_filter([
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
            'kind'       => $kind,
        ]));

        return $employee->load('activeEmployment');
    }

    // ==========================================
    // LANÇAMENTO (POST)
    // ==========================================

    public function testUsuarioComPermissaoPodeLancarMarcacao(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company);

        $this->postJson('/api/v1/time-entries', [
            'employeeId' => $employee->id,
            'punchedAt' => '2026-06-25 08:00:00',
            'type'       => 'entry',
        ])->assertCreated();

        $this->assertDatabaseHas('attendance.time_entries', [
            'companyId' => $company->id,
            'type'      => 'entry',
        ]);

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'companyId'  => $company->id,
            'employeeId' => $employee->id,
            'date'       => '2026-06-25',
        ]);
    }

    public function testUsuarioSemPermissaoNaoPodeLancarMarcacao(): void
    {
        $this->autenticarSemPermissao();

        $this->postJson('/api/v1/time-entries', [
            'employeeId' => fake()->uuid(),
            'punchedAt' => '2026-06-25 08:00:00',
            'type'       => 'entry',
        ])->assertForbidden();
    }

    public function testLancarMarcacoesCriaUmUnicoDia(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company);

        foreach (['08:00:00', '18:00:00'] as $i => $hora) {
            $this->postJson('/api/v1/time-entries', [
                'employeeId' => $employee->id,
                'punchedAt' => "2026-06-25 {$hora}",
                'type'       => $i === 0 ? 'entry' : 'exit',
            ])->assertCreated();
        }

        $this->assertDatabaseCount('attendance.daily_engagements', 1);
        $this->assertDatabaseCount('attendance.time_entries', 2);
    }

    public function testLancaLoteDeMarcacoesDeUmaVez(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company);

        // "Dia completo": os 4 punches da jornada num request só.
        $this->postJson('/api/v1/time-entries/batch', [
            'employeeId' => $employee->id,
            'entries'    => [
                ['punchedAt' => '2026-06-25T08:00:00-03:00', 'type' => 'entry'],
                ['punchedAt' => '2026-06-25T12:00:00-03:00', 'type' => 'exit'],
                ['punchedAt' => '2026-06-25T13:00:00-03:00', 'type' => 'entry'],
                ['punchedAt' => '2026-06-25T18:00:00-03:00', 'type' => 'exit'],
            ],
        ])->assertCreated();

        $this->assertDatabaseCount('attendance.time_entries', 4);
        $this->assertDatabaseCount('attendance.daily_engagements', 1);

        // 08–12 + 13–18 = 9h trabalhadas, num único recálculo.
        $this->assertDatabaseHas('attendance.daily_engagements', [
            'employeeId'     => $employee->id,
            'status'         => 'draft',
            'workedMinutes' => 540,
        ]);
    }

    public function testLoteComReplaceSubstituiMarcacoesDoDia(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company);

        // Dia com uma marcação avulsa lançada antes.
        $this->postJson('/api/v1/time-entries', [
            'employeeId' => $employee->id,
            'punchedAt' => '2026-06-25T09:30:00-03:00',
            'type'       => 'entry',
        ])->assertCreated();

        // "Dia completo" com replace: troca tudo pelas marcações da jornada.
        $this->postJson('/api/v1/time-entries/batch', [
            'employeeId' => $employee->id,
            'replace'    => true,
            'entries'    => [
                ['punchedAt' => '2026-06-25T08:00:00-03:00', 'type' => 'entry'],
                ['punchedAt' => '2026-06-25T12:00:00-03:00', 'type' => 'exit'],
                ['punchedAt' => '2026-06-25T13:00:00-03:00', 'type' => 'entry'],
                ['punchedAt' => '2026-06-25T18:00:00-03:00', 'type' => 'exit'],
            ],
        ])->assertCreated();

        // Só as 4 do lote — a avulsa das 09:30 foi substituída.
        $this->assertDatabaseCount('attendance.time_entries', 4);
        $this->assertDatabaseMissing('attendance.time_entries', [
            'punchedAt' => '2026-06-25 12:30:00',
        ]);

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'employeeId'     => $employee->id,
            'workedMinutes' => 540,
        ]);
    }

    public function testUsuarioSemPermissaoNaoPodeLancarLote(): void
    {
        $this->autenticarSemPermissao();

        $this->postJson('/api/v1/time-entries/batch', [
            'employeeId' => fake()->uuid(),
            'entries'    => [
                ['punchedAt' => '2026-06-25T08:00:00-03:00', 'type' => 'entry'],
            ],
        ])->assertForbidden();
    }

    public function testLancarMarcacaoCriaDiaComoRascunho(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company);

        $this->postJson('/api/v1/time-entries', [
            'employeeId' => $employee->id,
            'punchedAt' => '2026-06-25 08:00:00',
            'type'       => 'entry',
        ])->assertCreated();

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'employeeId' => $employee->id,
            'status'     => 'draft',
        ]);
    }

    public function testConverteMarcacaoParaUtc(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company);

        // Front envia com fuso; o back converte para UTC (08:00 -03:00 => 11:00 UTC).
        $this->postJson('/api/v1/time-entries', [
            'employeeId' => $employee->id,
            'punchedAt' => '2026-06-10T08:00:00-03:00',
            'type'       => 'entry',
        ])->assertCreated();

        $this->assertDatabaseHas('attendance.time_entries', [
            'punchedAt' => '2026-06-10 11:00:00',
        ]);

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'employeeId' => $employee->id,
            'date'       => '2026-06-10',
        ]);
    }

    public function testCalculaHorasTrabalhadasESaldo(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company);

        $marcacoes = [
            ['08:00:00', 'entry'],
            ['12:00:00', 'exit'],
            ['13:00:00', 'entry'],
            ['18:00:00', 'exit'],
        ];

        foreach ($marcacoes as [$hora, $type]) {
            $this->postJson('/api/v1/time-entries', [
                'employeeId' => $employee->id,
                'punchedAt' => "2026-06-25 {$hora}",
                'type'       => $type,
            ])->assertCreated();
        }

        $this->assertDatabaseHas('attendance.daily_engagements', [
            'employeeId'       => $employee->id,
            'workedMinutes'   => 540,
            'expectedMinutes' => 540,
            'balanceMinutes'  => 0,
        ]);
    }

    // ==========================================
    // CONFLITO DE HORÁRIO (CLT + diarista concorrentes no mesmo funcionário)
    // ==========================================

    public function testNaoPermiteDuasMarcacoesNoMesmoHorario(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company);

        $this->postJson('/api/v1/time-entries', [
            'employeeId' => $employee->id,
            'punchedAt' => '2026-06-25 08:00:00',
            'type'       => 'entry',
        ])->assertCreated();

        $this->postJson('/api/v1/time-entries', [
            'employeeId' => $employee->id,
            'punchedAt' => '2026-06-25 08:00:00',
            'type'       => 'entry',
        ])->assertConflict();

        $this->assertDatabaseCount('attendance.time_entries', 1);
    }

    public function testLoteRejeitaDuasMarcacoesNoMesmoHorario(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company);

        $this->postJson('/api/v1/time-entries/batch', [
            'employeeId' => $employee->id,
            'entries'    => [
                ['punchedAt' => '2026-06-25T08:00:00-03:00', 'type' => 'entry'],
                ['punchedAt' => '2026-06-25T08:00:00-03:00', 'type' => 'entry'],
            ],
        ])->assertConflict();

        $this->assertDatabaseCount('attendance.time_entries', 0);
    }

    public function testAtualizarMarcacaoParaHorarioJaOcupadoEhRejeitado(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', company: $company);

        $employee = $this->funcionarioComJornada($company);
        $day = DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $employee->id,
        ]);

        TimeEntry::factory()->create([
            'companyId'         => $company->id,
            'dailyEngagementId' => $day->id,
            'punchedAt'        => '2026-06-25 08:00:00',
            'type'              => 'entry',
        ]);

        $entry2 = TimeEntry::factory()->create([
            'companyId'         => $company->id,
            'dailyEngagementId' => $day->id,
            'punchedAt'        => '2026-06-25 18:00:00',
            'type'              => 'exit',
        ]);

        $this->putJson("/api/v1/time-entries/{$entry2->id}", [
            'punchedAt' => '2026-06-25 08:00:00',
        ])->assertConflict();
    }

    // ==========================================
    // DIÁRIA (implementação parcial)
    // ==========================================

    public function testDiaristaRecebeValorDeDiaria(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company, kind: EmploymentTypeEnum::DAYLI->value);

        $this->postJson('/api/v1/time-entries', [
            'employeeId' => $employee->id,
            'punchedAt' => '2026-06-25 08:00:00',
            'type'       => 'entry',
        ])->assertCreated();

        $this->postJson('/api/v1/time-entries', [
            'employeeId' => $employee->id,
            'punchedAt' => '2026-06-25 12:00:00',
            'type'       => 'exit',
        ])->assertCreated();

        $day = DailyEngagement::query()
            ->where('employeeId', $employee->id)
            ->first();

        $this->assertEquals(1.0, $day->diariaValue);
    }

    public function testVinculoPorHoraNaoRecebeDiaria(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComPermissao('attendance.timeEntries.create', company: $company);

        $employee = $this->funcionarioComJornada($company); // CLT

        $this->postJson('/api/v1/time-entries', [
            'employeeId' => $employee->id,
            'punchedAt' => '2026-06-25 08:00:00',
            'type'       => 'entry',
        ])->assertCreated();

        $day = DailyEngagement::query()
            ->where('employeeId', $employee->id)
            ->first();

        $this->assertNull($day->diariaValue);
    }

    // ==========================================
    // ATUALIZAÇÃO / EXCLUSÃO
    // ==========================================

    public function testOwnerPodeAtualizarMarcacao(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', company: $company);

        $employee = $this->funcionarioComJornada($company);
        $day = DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $employee->id,
        ]);
        $entry = TimeEntry::factory()->create([
            'companyId'         => $company->id,
            'dailyEngagementId' => $day->id,
            'punchedAt'        => '2026-06-25 08:00:00',
            'type'              => 'entry',
        ]);

        $this->putJson("/api/v1/time-entries/{$entry->id}", [
            'punchedAt' => '2026-06-25 09:00:00',
        ])->assertOk();

        $this->assertDatabaseHas('attendance.time_entries', [
            'id' => $entry->id,
        ]);
    }

    public function testFuncionarioComumNaoPodeAtualizarMarcacao(): void
    {
        $company = Company::factory()->create();

        $this->autenticarSemPermissao(company: $company);

        $entry = TimeEntry::factory()->create(['companyId' => $company->id]);

        $this->putJson("/api/v1/time-entries/{$entry->id}", [
            'punchedAt' => '2026-06-25 09:00:00',
        ])->assertForbidden();
    }

    public function testOwnerPodeExcluirMarcacao(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', company: $company);

        $employee = $this->funcionarioComJornada($company);
        $day = DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $employee->id,
        ]);
        $entry = TimeEntry::factory()->create([
            'companyId'         => $company->id,
            'dailyEngagementId' => $day->id,
        ]);

        $this->deleteJson("/api/v1/time-entries/{$entry->id}")->assertNoContent();

        $this->assertDatabaseMissing('attendance.time_entries', ['id' => $entry->id]);
    }

    public function testFuncionarioComumNaoPodeExcluirMarcacao(): void
    {
        $company = Company::factory()->create();

        $this->autenticarSemPermissao(company: $company);

        $entry = TimeEntry::factory()->create(['companyId' => $company->id]);

        $this->deleteJson("/api/v1/time-entries/{$entry->id}")->assertForbidden();
    }
}

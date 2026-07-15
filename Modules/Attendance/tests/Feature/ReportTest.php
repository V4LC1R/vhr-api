<?php

namespace Modules\Attendance\Tests\Feature;

use Modules\Attendance\Models\DailyEngagement;
use Modules\Core\Models\Company;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
use Tests\DBTestCase;

class ReportTest extends DBTestCase
{
    protected bool $seed = true;

    private const FROM = '2026-07-01';
    private const TO   = '2026-07-31';

    private function funcionarioComVinculo(Company $company, EmploymentTypeEnum $kind): Employee
    {
        $employee = Employee::factory()->create(['companyId' => $company->id]);

        Employment::factory()->hired()->create([
            'employeeId' => $employee->id,
            'kind'       => $kind->value,
        ]);

        return $employee;
    }

    // ==========================================
    // AUTORIZAÇÃO
    // ==========================================

    public function testUsuarioSemPermissaoNaoAcessaRelatorioGeral(): void
    {
        $this->autenticarSemPermissao();

        $this->getJson('/api/v1/reports/hours-summary?from=' . self::FROM . '&to=' . self::TO)
            ->assertForbidden();
    }

    public function testContadorAcessaRelatorioGeral(): void
    {
        $this->autenticarComRole('accountant');

        $this->getJson('/api/v1/reports/hours-summary?from=' . self::FROM . '&to=' . self::TO)
            ->assertOk();
    }

    public function testContadorNaoAcessaRelatorioDeFaltas(): void
    {
        $this->autenticarComRole('accountant');

        $this->getJson('/api/v1/reports/absences?from=' . self::FROM . '&to=' . self::TO)
            ->assertForbidden();
    }

    public function testContadorNaoAcessaRelatorioDeDiaristas(): void
    {
        $this->autenticarComRole('accountant');

        $this->getJson('/api/v1/reports/dayli-workers?from=' . self::FROM . '&to=' . self::TO)
            ->assertForbidden();
    }

    public function testOwnerAcessaOsTresRelatorios(): void
    {
        $this->autenticarComRole('owner');

        $this->getJson('/api/v1/reports/hours-summary?from=' . self::FROM . '&to=' . self::TO)->assertOk();
        $this->getJson('/api/v1/reports/absences?from=' . self::FROM . '&to=' . self::TO)->assertOk();
        $this->getJson('/api/v1/reports/dayli-workers?from=' . self::FROM . '&to=' . self::TO)->assertOk();
    }

    public function testValidaPeriodoObrigatorio(): void
    {
        $this->autenticarComRole('owner');

        $this->getJson('/api/v1/reports/hours-summary')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['from', 'to']);
    }

    // ==========================================
    // AGREGAÇÃO
    // ==========================================

    public function testRelatorioDeFaltasSoTrazQuemTemFaltaOuSaldoNegativo(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', $company);

        $comFalta = $this->funcionarioComVinculo($company, EmploymentTypeEnum::CLT);
        DailyEngagement::factory()->approved()->create([
            'companyId'       => $company->id,
            'employeeId'      => $comFalta->id,
            'date'            => '2026-07-10',
            'type'            => 'absence',
            'workedMinutes'   => 0,
            'expectedMinutes' => 480,
            'balanceMinutes'  => -480,
        ]);

        $semProblema = $this->funcionarioComVinculo($company, EmploymentTypeEnum::CLT);
        DailyEngagement::factory()->approved()->create([
            'companyId'       => $company->id,
            'employeeId'      => $semProblema->id,
            'date'            => '2026-07-10',
            'type'            => 'work',
            'workedMinutes'   => 480,
            'expectedMinutes' => 480,
            'balanceMinutes'  => 0,
        ]);

        $response = $this->getJson('/api/v1/reports/absences?from=' . self::FROM . '&to=' . self::TO)
            ->assertOk()
            ->json('data');

        $ids = collect($response)->pluck('employeeId');

        $this->assertTrue($ids->contains($comFalta->id));
        $this->assertFalse($ids->contains($semProblema->id));

        $linha = collect($response)->firstWhere('employeeId', $comFalta->id);
        $this->assertSame(1, $linha['absenceDays']);
        $this->assertSame(-480, $linha['negativeBalanceMinutes']);
        $this->assertSame(-480, $linha['balanceMinutes']);
    }

    public function testRelatorioDeDiaristasConverteMinutosEmHoraDecimal(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', $company);

        $diarista = $this->funcionarioComVinculo($company, EmploymentTypeEnum::DAYLI);
        DailyEngagement::factory()->approved()->create([
            'companyId'       => $company->id,
            'employeeId'      => $diarista->id,
            'date'            => '2026-07-10',
            'type'            => 'work',
            'workedMinutes'   => 90, // 1h30
            'expectedMinutes' => 480,
            'balanceMinutes'  => -390,
            'diariaValue'     => 1.0,
        ]);

        $clt = $this->funcionarioComVinculo($company, EmploymentTypeEnum::CLT);
        DailyEngagement::factory()->approved()->create([
            'companyId'       => $company->id,
            'employeeId'      => $clt->id,
            'date'            => '2026-07-10',
            'type'            => 'work',
            'workedMinutes'   => 480,
            'expectedMinutes' => 480,
            'balanceMinutes'  => 0,
        ]);

        $response = $this->getJson('/api/v1/reports/dayli-workers?from=' . self::FROM . '&to=' . self::TO)
            ->assertOk()
            ->json('data');

        $ids = collect($response)->pluck('employeeId');
        $this->assertTrue($ids->contains($diarista->id));
        $this->assertFalse($ids->contains($clt->id));

        $linha = collect($response)->firstWhere('employeeId', $diarista->id);
        $this->assertSame(1.5, $linha['workedHoursDecimal']);
        $this->assertSame(1, $linha['diasTrabalhados']);
        $this->assertEquals(1.0, $linha['diariaValueTotal']);
    }

    public function testRelatorioGeralSoConsideraDiasAprovados(): void
    {
        $company = Company::factory()->create();
        $this->autenticarComRole('owner', $company);

        $employee = $this->funcionarioComVinculo($company, EmploymentTypeEnum::CLT);

        DailyEngagement::factory()->approved()->create([
            'companyId'       => $company->id,
            'employeeId'      => $employee->id,
            'date'            => '2026-07-10',
            'type'            => 'work',
            'workedMinutes'   => 480,
            'expectedMinutes' => 480,
            'balanceMinutes'  => 0,
        ]);

        // Pendente — não deve entrar na soma.
        DailyEngagement::factory()->create([
            'companyId'       => $company->id,
            'employeeId'      => $employee->id,
            'date'            => '2026-07-11',
            'type'            => 'work',
            'status'          => 'pending',
            'workedMinutes'   => 480,
            'expectedMinutes' => 480,
            'balanceMinutes'  => 0,
        ]);

        $linha = collect(
            $this->getJson('/api/v1/reports/hours-summary?from=' . self::FROM . '&to=' . self::TO)
                ->assertOk()
                ->json('data')
        )->firstWhere('employeeId', $employee->id);

        $this->assertSame(480, $linha['workedMinutes']);
    }
}

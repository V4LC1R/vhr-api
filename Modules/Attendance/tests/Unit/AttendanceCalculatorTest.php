<?php

namespace Modules\Attendance\Tests\Unit;

use Modules\Attendance\Models\DailyEngagement;
use Modules\Attendance\Models\TimeEntry;
use Modules\Attendance\Support\AttendanceCalculator;
use Modules\Core\Models\Company;
use Modules\Job\Enums\EmploymentTypeEnum;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Employment;
use Modules\Job\Models\Workload;
use Tests\DBTestCase;

class AttendanceCalculatorTest extends DBTestCase
{
    /**
     * Monta um dia totalmente ligado (empresa, funcionário, vínculo ativo, jornada)
     * com as marcações informadas. Jornada padrão: 08:00–18:00, intervalo 12:00–13:00
     * => esperado de 540 min.
     *
     * @param  array<int, array{0:string,1:string}>  $punches  pares [punchedAt, type]
     */
    private function novoDia(
        array $punches = [],
        string $type = 'work',
        ?string $kind = null,
        bool $comJornada = true,
    ): DailyEngagement {
        $company  = Company::factory()->create();
        $employee = Employee::factory()->create(['companyId' => $company->id]);
        $workload = Workload::factory()->create(['companyId' => $company->id]);

        Employment::factory()->create(array_filter([
            'employeeId' => $employee->id,
            'workloadId' => $workload->id,
            'kind'       => $kind,
        ]));

        $day = DailyEngagement::factory()->create([
            'companyId'  => $company->id,
            'employeeId' => $employee->id,
            'workloadId' => $comJornada ? $workload->id : null,
            'type'       => $type,
        ]);

        foreach ($punches as [$punchedAt, $punchType]) {
            TimeEntry::factory()->create([
                'companyId'         => $company->id,
                'dailyEngagementId' => $day->id,
                'punchedAt'        => $punchedAt,
                'type'              => $punchType,
            ]);
        }

        return $day;
    }

    private function calcular(DailyEngagement $day): DailyEngagement
    {
        app(AttendanceCalculator::class)->recalculate($day);

        return $day->refresh();
    }

    // ==========================================
    // HORAS / SALDO
    // ==========================================

    public function testDiaCompletoGeraSaldoZero(): void
    {
        $day = $this->calcular($this->novoDia([
            ['2026-06-10 08:00:00', 'entry'],
            ['2026-06-10 12:00:00', 'exit'],
            ['2026-06-10 13:00:00', 'entry'],
            ['2026-06-10 18:00:00', 'exit'],
        ]));

        $this->assertSame(540, $day->workedMinutes);
        $this->assertSame(540, $day->expectedMinutes);
        $this->assertSame(0, $day->balanceMinutes);
    }

    public function testHoraExtraGeraSaldoPositivo(): void
    {
        $day = $this->calcular($this->novoDia([
            ['2026-06-10 08:00:00', 'entry'],
            ['2026-06-10 12:00:00', 'exit'],
            ['2026-06-10 13:00:00', 'entry'],
            ['2026-06-10 19:00:00', 'exit'],
        ]));

        $this->assertSame(600, $day->workedMinutes);
        $this->assertSame(60, $day->balanceMinutes);
    }

    public function testSemMarcacoesGeraSaldoNegativo(): void
    {
        $day = $this->calcular($this->novoDia([]));

        $this->assertSame(0, $day->workedMinutes);
        $this->assertSame(540, $day->expectedMinutes);
        $this->assertSame(-540, $day->balanceMinutes);
    }

    // ==========================================
    // MARCAÇÕES EM ABERTO (só soma par entrada→saída)
    // ==========================================

    public function testEntradaSemSaidaNaoCalcula(): void
    {
        $day = $this->calcular($this->novoDia([
            ['2026-06-10 08:00:00', 'entry'],
        ]));

        $this->assertSame(0, $day->workedMinutes);
    }

    public function testUltimaEntradaEmAbertoEhIgnorada(): void
    {
        $day = $this->calcular($this->novoDia([
            ['2026-06-10 08:00:00', 'entry'],
            ['2026-06-10 12:00:00', 'exit'],
            ['2026-06-10 13:00:00', 'entry'], // fica em aberto
        ]));

        $this->assertSame(240, $day->workedMinutes);
    }

    public function testSaidaSemEntradaEhIgnorada(): void
    {
        $day = $this->calcular($this->novoDia([
            ['2026-06-10 12:00:00', 'exit'],
        ]));

        $this->assertSame(0, $day->workedMinutes);
    }

    public function testEntradasDuplicadasMantemAPrimeira(): void
    {
        $day = $this->calcular($this->novoDia([
            ['2026-06-10 08:00:00', 'entry'],
            ['2026-06-10 08:30:00', 'entry'], // ignorada (já há entrada aberta)
            ['2026-06-10 12:00:00', 'exit'],
        ]));

        // 08:00 -> 12:00 = 240 (não 08:30 -> 12:00 = 210)
        $this->assertSame(240, $day->workedMinutes);
    }

    public function testMarcacaoCruzandoMeiaNoiteCalculaCorreto(): void
    {
        // sem jornada para isolar o cálculo de worked (esperado 0)
        $day = $this->calcular($this->novoDia(
            punches: [
                ['2026-06-10 22:00:00', 'entry'],
                ['2026-06-11 02:00:00', 'exit'],
            ],
            comJornada: false,
        ));

        $this->assertSame(240, $day->workedMinutes);
        $this->assertSame(0, $day->expectedMinutes);
        $this->assertSame(240, $day->balanceMinutes);
    }

    public function testSemJornadaEsperadoZero(): void
    {
        $day = $this->calcular($this->novoDia(
            punches: [
                ['2026-06-10 08:00:00', 'entry'],
                ['2026-06-10 12:00:00', 'exit'],
            ],
            comJornada: false,
        ));

        $this->assertSame(240, $day->workedMinutes);
        $this->assertSame(0, $day->expectedMinutes);
        $this->assertSame(240, $day->balanceMinutes);
    }

    // ==========================================
    // EXCEÇÕES
    // ==========================================

    public function testFolgaZeraEsperado(): void
    {
        $day = $this->calcular($this->novoDia(type: 'day_off'));

        $this->assertSame(0, $day->expectedMinutes);
        $this->assertSame(0, $day->workedMinutes);
        $this->assertSame(0, $day->balanceMinutes);
    }

    public function testFeriadoZeraEsperado(): void
    {
        $day = $this->calcular($this->novoDia(type: 'holiday'));

        $this->assertSame(0, $day->expectedMinutes);
        $this->assertSame(0, $day->balanceMinutes);
    }

    public function testAtestadoAbonaAJornada(): void
    {
        // atestado: conta como trabalhado mesmo sem marcações
        $day = $this->calcular($this->novoDia(type: 'medical'));

        $this->assertSame(540, $day->expectedMinutes);
        $this->assertSame(540, $day->workedMinutes);
        $this->assertSame(0, $day->balanceMinutes);
    }

    public function testFaltaGeraSaldoNegativo(): void
    {
        $day = $this->calcular($this->novoDia(type: 'absence'));

        $this->assertSame(0, $day->workedMinutes);
        $this->assertSame(540, $day->expectedMinutes);
        $this->assertSame(-540, $day->balanceMinutes);
    }

    // ==========================================
    // DIÁRIA (parcial — regra provisória por presença)
    // ==========================================

    public function testDiaristaComPresencaRecebeDiaria(): void
    {
        $day = $this->calcular($this->novoDia(
            punches: [
                ['2026-06-10 08:00:00', 'entry'],
                ['2026-06-10 12:00:00', 'exit'],
            ],
            kind: EmploymentTypeEnum::DAYLI->value,
        ));

        $this->assertSame(1.0, $day->diariaValue);
    }

    public function testDiaristaSemPresencaNaoRecebeDiaria(): void
    {
        $day = $this->calcular($this->novoDia(
            kind: EmploymentTypeEnum::DAYLI->value,
        ));

        $this->assertSame(0.0, $day->diariaValue);
    }

    public function testVinculoPorHoraNaoRecebeDiaria(): void
    {
        $day = $this->calcular($this->novoDia(
            punches: [
                ['2026-06-10 08:00:00', 'entry'],
                ['2026-06-10 12:00:00', 'exit'],
            ],
        ));

        $this->assertNull($day->diariaValue);
    }
}

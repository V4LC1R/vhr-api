<?php

declare(strict_types=1);

namespace Modules\Attendance\Support;

use Illuminate\Support\Carbon;
use Modules\Attendance\Enums\TimeEntryTypeEnum;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Job\Enums\EmploymentTypeEnum;

/**
 * Calcula e persiste os números cacheados de um dia (DailyEngagement):
 * workedMinutes, expectedMinutes, balanceMinutes e diariaValue.
 *
 * É a única fonte de cálculo — chamado em toda mutação de marcação ou de tipo do dia.
 */
class AttendanceCalculator
{
    public function recalculate(DailyEngagement $engagement): DailyEngagement
    {
        $engagement->loadMissing([
            'timeEntries',
            'workload',
            'employee.activeEmployment',
        ]);

        $type     = $engagement->type;
        $expected = $this->expectedMinutes($engagement);
        $worked   = $type->isAbonado()
            ? $expected
            : $this->workedMinutes($engagement);

        $balance = $worked - $expected;

        $engagement->forceFill([
            'workedMinutes'   => $worked,
            'expectedMinutes' => $expected,
            'balanceMinutes'  => $balance,
            'diariaValue'     => $this->diariaValue($engagement, $worked),
        ])->save();

        return $engagement;
    }

    /**
     * Soma os spans entrada→saída das marcações ordenadas por horário.
     */
    private function workedMinutes(DailyEngagement $engagement): int
    {
        $entries = $engagement->timeEntries->sortBy('punchedAt')->values();

        $worked = 0;
        $openEntry = null;

        foreach ($entries as $entry) {
            if ($entry->type === TimeEntryTypeEnum::ENTRY) {
                // Mantém a primeira entrada aberta; entradas repetidas são ignoradas.
                $openEntry ??= $entry->punchedAt;

                continue;
            }

            // Saída só soma se houver uma entrada em aberto; saída solta é ignorada.
            if ($entry->type === TimeEntryTypeEnum::EXIT && $openEntry !== null) {
                $worked += (int) abs($openEntry->diffInMinutes($entry->punchedAt));
                $openEntry = null;
            }
        }

        // Marcação que ficou em aberto (entrada sem saída) não é calculada.
        return $worked;
    }

    /**
     * Jornada esperada do dia, a partir do snapshot de workload.
     * Folga/feriado não têm jornada esperada.
     */
    private function expectedMinutes(DailyEngagement $engagement): int
    {
        if (! $engagement->type->hasExpected() || ! $engagement->workload) {
            return 0;
        }

        $workload = $engagement->workload;

        $work = abs(
            Carbon::parse($workload->leftTime)
                ->diffInMinutes(Carbon::parse($workload->entryTime))
        );

        $interval = abs(
            Carbon::parse($workload->intervalEndAt)
                ->diffInMinutes(Carbon::parse($workload->intervalStartAt))
        );

        return (int) max(0, $work - $interval);
    }

    /**
     * Valor da diária — só para vínculo `dayli`; null para vínculos por hora.
     * A regra de pagamento ainda é provisória (ver DiariaRule).
     */
    private function diariaValue(DailyEngagement $engagement, int $worked): ?float
    {
        $kind = $engagement->employee?->activeEmployment?->kind;

        if ($kind !== EmploymentTypeEnum::DAYLI) {
            return null;
        }

        return DiariaRule::provisional($engagement->type, $worked);
    }
}

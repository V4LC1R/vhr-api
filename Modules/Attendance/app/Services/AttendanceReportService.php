<?php

namespace Modules\Attendance\Services;

use Illuminate\Support\Collection;
use Modules\Attendance\Enums\DailyEngagementStatusEnum;
use Modules\Attendance\Enums\DailyEngagementTypeEnum;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Job\Models\Employee;

/**
 * Agregação por funcionário usada pelas 3 telas de relatório (geral, faltas/horas
 * negativas, diaristas) — sempre sobre dias APROVADOS (dado fechado). Cada tela usa
 * um subconjunto dos filtros (`kinds`, `onlyExceptions`) e das colunas do resultado.
 */
class AttendanceReportService
{
    public function summary(string $companyId, array $filters): Collection
    {
        $query = DailyEngagement::query()
            ->where('companyId', $companyId)
            ->where('status', DailyEngagementStatusEnum::APPROVED->value)
            ->whereBetween('date', [$filters['from'], $filters['to']]);

        if (! empty($filters['kinds'])) {
            $query->whereHas(
                'employee.activeEmployment',
                fn ($q) => $q->whereIn('kind', $filters['kinds'])
            );
        }

        if (! empty($filters['name'])) {
            $query->whereHas(
                'employee.person',
                fn ($q) => $q->where('name', 'ILIKE', "%{$filters['name']}%")
            );
        }

        $rows = $query
            ->selectRaw(
                '"employeeId",
                SUM("workedMinutes") as "workedMinutes",
                SUM("expectedMinutes") as "expectedMinutes",
                SUM("balanceMinutes") as "balanceMinutes",
                SUM(CASE WHEN "balanceMinutes" < 0 THEN "balanceMinutes" ELSE 0 END) as "negativeBalanceMinutes",
                COUNT(CASE WHEN "type" = ? THEN 1 END) as "absenceDays",
                COUNT(CASE WHEN "workedMinutes" > 0 THEN 1 END) as "diasTrabalhados",
                SUM("diariaValue") as "diariaValueTotal"',
                [DailyEngagementTypeEnum::ABSENCE->value]
            )
            ->groupBy('employeeId')
            ->get();

        if (! empty($filters['onlyExceptions'])) {
            $rows = $rows
                ->filter(fn ($row) => $row->absenceDays > 0 || $row->negativeBalanceMinutes < 0)
                ->values();
        }

        $employees = Employee::query()
            ->whereIn('id', $rows->pluck('employeeId'))
            ->with(['person', 'activeEmployment'])
            ->get()
            ->keyBy('id');

        return $rows
            ->map(function ($row) use ($employees) {
                $employee = $employees->get($row->employeeId);

                return [
                    'employeeId'             => $row->employeeId,
                    'registerNumber'         => $employee?->registerNumber,
                    'personName'             => $employee?->person?->name,
                    'kind'                   => $employee?->activeEmployment?->kind?->value,
                    'workedMinutes'          => (int) $row->workedMinutes,
                    'workedHoursDecimal'     => round($row->workedMinutes / 60, 2),
                    'expectedMinutes'        => (int) $row->expectedMinutes,
                    'balanceMinutes'         => (int) $row->balanceMinutes,
                    'negativeBalanceMinutes' => (int) $row->negativeBalanceMinutes,
                    'absenceDays'            => (int) $row->absenceDays,
                    'diasTrabalhados'        => (int) $row->diasTrabalhados,
                    'diariaValueTotal'       => $row->diariaValueTotal !== null ? (float) $row->diariaValueTotal : null,
                ];
            })
            ->sortBy('personName')
            ->values();
    }
}

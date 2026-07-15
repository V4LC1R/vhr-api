import { formatBalance, formatMinutes } from "@/feature/attendance/lib/time"
import { useReportPage } from "../hooks/useReportPage"
import { ReportFiltersBar } from "../components/report-filters"
import { ReportExportMenu } from "../components/report-export-menu"
import { ReportTable, ReportColumn } from "../components/report-table"
import { ReportRow } from "../types/types"

const COLUMNS: ReportColumn[] = [
    { key: "registerNumber", label: "Matrícula" },
    { key: "personName", label: "Colaborador" },
    { key: "absenceDays", label: "Faltas", align: "right" },
    {
        key: "negativeBalanceMinutes",
        label: "Horas negativas",
        align: "right",
        format: (row: ReportRow) => `-${formatMinutes(row.negativeBalanceMinutes)}`,
    },
    {
        key: "balanceMinutes",
        label: "Saldo do período",
        align: "right",
        format: (row: ReportRow) => formatBalance(row.balanceMinutes),
    },
]

/** Owner-only — só traz quem teve falta ou saldo negativo no período (`onlyExceptions`). */
export function AbsencesReportView() {
    const { range, setRange, name, setName, rows, isLoading, isoRange } = useReportPage(
        "/api/v1/reports/absences"
    )

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <ReportFiltersBar range={range} onRangeChange={setRange} name={name} onNameChange={setName} />
                {isoRange && (
                    <ReportExportMenu endpoint="/api/v1/reports/absences/export" range={isoRange} name={name} />
                )}
            </div>
            <ReportTable
                rows={rows}
                columns={COLUMNS}
                isLoading={isLoading}
                emptyMessage="Ninguém com falta ou saldo negativo no período."
                range={isoRange}
            />
        </div>
    )
}

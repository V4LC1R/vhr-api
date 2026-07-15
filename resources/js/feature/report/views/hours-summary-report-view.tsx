import { formatBalance } from "@/feature/attendance/lib/time"
import { useReportPage } from "../hooks/useReportPage"
import { ReportFiltersBar } from "../components/report-filters"
import { ReportExportMenu } from "../components/report-export-menu"
import { ReportTable, ReportColumn } from "../components/report-table"
import { EMPLOYMENT_KIND_LABELS } from "../lib/labels"
import { ReportRow } from "../types/types"

const COLUMNS: ReportColumn[] = [
    { key: "registerNumber", label: "Matrícula" },
    { key: "personName", label: "Colaborador" },
    { key: "kind", label: "Vínculo", format: (row: ReportRow) => EMPLOYMENT_KIND_LABELS[row.kind ?? ""] ?? "—" },
    { key: "workedHoursDecimal", label: "Horas trabalhadas", align: "right" },
    {
        key: "balanceMinutes",
        label: "Saldo do período",
        align: "right",
        format: (row: ReportRow) => formatBalance(row.balanceMinutes),
    },
    { key: "absenceDays", label: "Faltas", align: "right" },
]

export function HoursSummaryReportView() {
    const { range, setRange, name, setName, rows, isLoading, isoRange } = useReportPage(
        "/api/v1/reports/hours-summary"
    )

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <ReportFiltersBar range={range} onRangeChange={setRange} name={name} onNameChange={setName} />
                {isoRange && (
                    <ReportExportMenu
                        endpoint="/api/v1/reports/hours-summary/export"
                        range={isoRange}
                        name={name}
                    />
                )}
            </div>
            <ReportTable
                rows={rows}
                columns={COLUMNS}
                isLoading={isLoading}
                emptyMessage="Nenhum dado aprovado no período."
                range={isoRange}
            />
        </div>
    )
}

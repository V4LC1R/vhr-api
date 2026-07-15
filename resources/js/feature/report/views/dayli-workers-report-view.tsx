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
    { key: "diasTrabalhados", label: "Dias trabalhados", align: "right" },
    { key: "workedHoursDecimal", label: "Horas trabalhadas", align: "right" },
    {
        key: "diariaValueTotal",
        label: "Valor diárias",
        align: "right",
        format: (row: ReportRow) => (row.diariaValueTotal !== null ? row.diariaValueTotal.toString() : "—"),
    },
]

/**
 * Owner-only — diaristas, temporários e freelancers juntos (mesmo agrupamento "temps"
 * da fila de aprovações). Só `dayli` tem diária calculada automaticamente; pra
 * temporary/freelancer as "Horas trabalhadas" (fator decimal, ex.: 1,5) servem pra
 * multiplicar manualmente pelo valor combinado.
 */
export function DayliWorkersReportView() {
    const { range, setRange, name, setName, rows, isLoading, isoRange } = useReportPage(
        "/api/v1/reports/dayli-workers"
    )

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <ReportFiltersBar range={range} onRangeChange={setRange} name={name} onNameChange={setName} />
                {isoRange && (
                    <ReportExportMenu
                        endpoint="/api/v1/reports/dayli-workers/export"
                        range={isoRange}
                        name={name}
                    />
                )}
            </div>
            <ReportTable
                rows={rows}
                columns={COLUMNS}
                isLoading={isLoading}
                emptyMessage="Nenhum diarista/temporário com dia aprovado no período."
                range={isoRange}
            />
        </div>
    )
}

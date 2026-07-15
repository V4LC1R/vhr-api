import * as React from "react"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { ChevronRightIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Badge } from "@/components/ui/badge"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import { DayPairs } from "@/feature/attendance/components/approvals/employee-days"
import { useListDailyEngagements } from "@/feature/attendance/hooks/useListDailyEngagements"
import { DAY_TYPE_LABELS, DAY_TYPE_VARIANTS } from "@/feature/attendance/lib/labels"
import { formatBalance } from "@/feature/attendance/lib/time"
import { ReportRow } from "../types/types"

export interface ReportColumn {
    key: keyof ReportRow
    label: string
    align?: "left" | "right"
    format?: (row: ReportRow) => React.ReactNode
}

interface ReportTableProps {
    rows: ReportRow[]
    columns: ReportColumn[]
    isLoading?: boolean
    emptyMessage: string
    /** Intervalo usado pra buscar os dias ao expandir uma linha. */
    range: { from: string; to: string } | null
}

function ReportRowDetail({ employeeId, range }: { employeeId: string; range: { from: string; to: string } }) {
    const { list, isLoadingDays, data } = useListDailyEngagements()

    React.useEffect(() => {
        list({
            filter: { employeeId, dateRange: `${range.from},${range.to}` },
            per_page: 100,
            sort: "date",
        })
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [employeeId, range.from, range.to])

    if (isLoadingDays) {
        return <p className="p-3 text-sm text-muted-foreground">Carregando dias...</p>
    }

    if (!data || data.length === 0) {
        return <p className="p-3 text-sm text-muted-foreground">Nenhum dia aprovado no período.</p>
    }

    return (
        <div className="flex flex-col gap-1 p-3">
            {data.map((day) => (
                <div
                    key={day.id}
                    className="flex flex-wrap items-center gap-2 rounded-lg border bg-background px-2 py-1.5"
                >
                    <span className="w-24 whitespace-nowrap text-sm font-medium capitalize">
                        {day.date && format(new Date(`${day.date}T00:00:00`), "dd/MM EEEEEE", { locale: ptBR })}
                    </span>

                    <Badge variant={DAY_TYPE_VARIANTS[day.type]}>{DAY_TYPE_LABELS[day.type]}</Badge>

                    <div className="flex min-w-0 flex-1 flex-wrap items-center gap-1">
                        <DayPairs day={day} />
                    </div>

                    <span
                        className={cn(
                            "w-16 text-right text-sm tabular-nums",
                            day.balanceMinutes < 0 && "text-destructive",
                            day.balanceMinutes > 0 && "text-primary"
                        )}
                    >
                        {formatBalance(day.balanceMinutes)}
                    </span>
                </div>
            ))}
        </div>
    )
}

export function ReportTable({ rows, columns, isLoading, emptyMessage, range }: ReportTableProps) {
    const [expandedId, setExpandedId] = React.useState<string | null>(null)

    return (
        <div className="min-h-0 flex-1 overflow-auto rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-8" />
                        {columns.map((col) => (
                            <TableHead key={col.key} className={col.align === "right" ? "text-right" : undefined}>
                                {col.label}
                            </TableHead>
                        ))}
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {isLoading && (
                        <TableRow>
                            <TableCell colSpan={columns.length + 1} className="text-center text-muted-foreground">
                                Carregando...
                            </TableCell>
                        </TableRow>
                    )}

                    {!isLoading && rows.length === 0 && (
                        <TableRow>
                            <TableCell colSpan={columns.length + 1} className="text-center text-muted-foreground">
                                {emptyMessage}
                            </TableCell>
                        </TableRow>
                    )}

                    {!isLoading &&
                        rows.map((row) => (
                            <React.Fragment key={row.employeeId}>
                                <TableRow
                                    className="cursor-pointer"
                                    onClick={() =>
                                        setExpandedId(expandedId === row.employeeId ? null : row.employeeId)
                                    }
                                >
                                    <TableCell>
                                        <ChevronRightIcon
                                            className={cn(
                                                "size-4 text-muted-foreground transition-transform",
                                                expandedId === row.employeeId && "rotate-90"
                                            )}
                                        />
                                    </TableCell>
                                    {columns.map((col) => (
                                        <TableCell
                                            key={col.key}
                                            className={col.align === "right" ? "text-right tabular-nums" : undefined}
                                        >
                                            {col.format ? col.format(row) : (row[col.key] ?? "—")}
                                        </TableCell>
                                    ))}
                                </TableRow>

                                {expandedId === row.employeeId && range && (
                                    <TableRow>
                                        <TableCell colSpan={columns.length + 1} className="bg-muted/30 p-0">
                                            <ReportRowDetail employeeId={row.employeeId} range={range} />
                                        </TableCell>
                                    </TableRow>
                                )}
                            </React.Fragment>
                        ))}
                </TableBody>
            </Table>
        </div>
    )
}

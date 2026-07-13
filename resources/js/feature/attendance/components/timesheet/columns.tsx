import { ColumnDef } from "@tanstack/react-table"
import { format, isWeekend } from "date-fns"
import { ptBR } from "date-fns/locale"
import { CalendarCogIcon, SendIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import {
    DAILY_ENGAGEMENT_TYPES,
    DailyEngagement,
    DailyEngagementType,
} from "@/types/dailyEngagement/types"
import { TimeEntry, TimeEntryType } from "@/types/timeEntry/types"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import { DAY_STATUS_LABELS, DAY_STATUS_VARIANTS, DAY_TYPE_LABELS } from "../../lib/labels"
import { formatBalance, formatMinutes } from "../../lib/time"
import { PunchInline } from "./punch-inline"

/** Uma linha por dia do mês — `day` só existe quando há lançamento. */
export type TimesheetRow = {
    date: string
    dayDate: Date
    day?: DailyEngagement
}

export type TimesheetTotals = {
    worked: number
    expected: number
    balance: number
    diarias: number
}

export type TimesheetColumnOptions = {
    /** Vínculo dayli: mostra a coluna Diária (valor por dia + total no rodapé). */
    showDiaria?: boolean
    /** Vínculo não-CLT: o lançamento rápido vira "Presente". */
    presenceMode?: boolean
}

function formatDiaria(value: number | null | undefined): string {
    if (value === null || value === undefined) return "—"

    return value.toLocaleString("pt-BR")
}

export interface TimesheetColumnActions {
    canFullDay: boolean
    isSaving?: boolean
    onAddPunch: (date: string, time: string, type: TimeEntryType) => Promise<boolean>
    onUpdatePunchTime: (punch: TimeEntry, time: string) => void
    onTogglePunchType: (punch: TimeEntry) => void
    onDeletePunch: (punch: TimeEntry) => void
    onFullDay: (date: string) => void
    onEditDayType: (date: string, day?: DailyEngagement) => void
    onChangeDayType: (date: string, type: DailyEngagementType) => void
    onSubmitDay: (day: DailyEngagement) => void
}

function statusTooltip(day: DailyEngagement): string | undefined {
    if (!day.approval.byName) return undefined

    if (day.status === "approved") return `Aprovado por ${day.approval.byName}`
    if (day.status === "rejected") return `Rejeitado por ${day.approval.byName}`

    return undefined
}

export function getTimesheetColumns(
    isMobile: boolean,
    totals: TimesheetTotals,
    actions: TimesheetColumnActions,
    options: TimesheetColumnOptions = {}
): ColumnDef<TimesheetRow>[] {
    const columns: ColumnDef<TimesheetRow>[] = [
        {
            id: "day",
            header: "Dia",
            size: 72,
            footer: () => "Total do mês",
            cell: ({ row }) => (
                <span
                    className={cn(
                        "whitespace-nowrap font-medium capitalize",
                        isWeekend(row.original.dayDate) && "text-muted-foreground"
                    )}
                >
                    {format(row.original.dayDate, "dd EEEEEE", { locale: ptBR })}
                </span>
            ),
        },
    ]

    if (!isMobile) {
        columns.push({
            id: "type",
            header: "Tipo",
            size: 120,
            cell: ({ row }) => (
                <Select
                    items={DAY_TYPE_LABELS}
                    // dia sem lançamento é implicitamente "work"; selecionar cria o dia
                    value={row.original.day?.type ?? "work"}
                    onValueChange={(type) =>
                        actions.onChangeDayType(row.original.date, type as DailyEngagementType)
                    }
                >
                    <SelectTrigger
                        size="sm"
                        className="h-7 w-28 text-xs"
                        disabled={actions.isSaving}
                        aria-label="Tipo do dia"
                    >
                        <SelectValue placeholder="—" />
                    </SelectTrigger>
                    <SelectContent>
                        {DAILY_ENGAGEMENT_TYPES.map((type) => (
                            <SelectItem key={type} value={type}>
                                {DAY_TYPE_LABELS[type]}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            ),
        })
    }

    columns.push({
        // sem `size` — flexiona pra ocupar o espaço restante (size é px fixo no DataTable)
        id: "punches",
        header: "Marcações",
        cell: ({ row }) => (
            <PunchInline
                date={row.original.date}
                day={row.original.day}
                canFullDay={actions.canFullDay}
                presenceMode={options.presenceMode}
                isSaving={actions.isSaving}
                onAdd={actions.onAddPunch}
                onUpdateTime={actions.onUpdatePunchTime}
                onToggleType={actions.onTogglePunchType}
                onDelete={actions.onDeletePunch}
                onFullDay={actions.onFullDay}
            />
        ),
    })

    if (!isMobile) {
        columns.push({
            id: "worked",
            header: () => <div className="text-right">Trabalhado</div>,
            size: 88,
            footer: () => (
                <div className="text-right tabular-nums">{formatMinutes(totals.worked)}</div>
            ),
            cell: ({ row }) => (
                <div className="text-right tabular-nums">
                    {row.original.day ? formatMinutes(row.original.day.workedMinutes) : "—"}
                </div>
            ),
        })

        columns.push({
            id: "expected",
            header: () => <div className="text-right">Esperado</div>,
            size: 20,
            footer: () => (
                <div className="text-right tabular-nums">{formatMinutes(totals.expected)}</div>
            ),
            cell: ({ row }) => (
                <div className="text-right tabular-nums">
                    {row.original.day ? formatMinutes(row.original.day.expectedMinutes) : "—"}
                </div>
            ),
        })
    }

    columns.push({
        id: "balance",
        header: () => <div className="text-right">Saldo</div>,
        size: 80,
        footer: () => (
            <div
                className={cn(
                    "text-right tabular-nums",
                    totals.balance < 0 && "text-destructive",
                    totals.balance > 0 && "text-primary"
                )}
            >
                {formatBalance(totals.balance)}
            </div>
        ),
        cell: ({ row }) => {
            const { day } = row.original

            return (
                <div
                    className={cn(
                        "text-right tabular-nums",
                        day && day.balanceMinutes < 0 && "text-destructive",
                        day && day.balanceMinutes > 0 && "text-primary"
                    )}
                >
                    {day ? formatBalance(day.balanceMinutes) : "—"}
                </div>
            )
        },
    })

    if (options.showDiaria) {
        columns.push({
            id: "diaria",
            header: () => <div className="text-right">Diária</div>,
            size: 72,
            footer: () => (
                <div className="text-right tabular-nums">{formatDiaria(totals.diarias)}</div>
            ),
            cell: ({ row }) => (
                <div className="text-right tabular-nums">
                    {formatDiaria(row.original.day?.diariaValue)}
                </div>
            ),
        })
    }

    if (!isMobile) {
        columns.push({
            id: "status",
            header: "Status",
            size: 20,
            cell: ({ row }) =>
                row.original.day && (
                    <Badge
                        variant={DAY_STATUS_VARIANTS[row.original.day.status]}
                        title={statusTooltip(row.original.day)}
                    >
                        {DAY_STATUS_LABELS[row.original.day.status]}
                    </Badge>
                ),
        })
    }

    columns.push({
        id: "actions",
        size: 72,
        header: () => null,
        cell: ({ row }) => {
            const { day } = row.original

            return (
                <div className="flex items-center justify-end gap-0.5">
                    {day?.status === "draft" && (
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            onClick={() => actions.onSubmitDay(day)}
                            title="Enviar p/ aprovação"
                            aria-label="Enviar dia p/ aprovação"
                        >
                            <SendIcon className="text-primary" />
                        </Button>
                    )}
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        onClick={() => actions.onEditDayType(row.original.date, day)}
                        title="Tipo do dia (folga, feriado, atestado, falta)"
                        aria-label="Tipo do dia"
                    >
                        <CalendarCogIcon />
                    </Button>
                </div>
            )
        },
    })

    return columns
}

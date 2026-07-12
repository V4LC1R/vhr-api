import { useMemo } from "react"
import { eachDayOfInterval, endOfMonth, format, isToday, isWeekend, startOfMonth,isSameMonth } from "date-fns"
import { ptBR } from "date-fns/locale"
import { CalendarCogIcon, SendIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { useIsMobile } from "@/hooks/use-mobile"
import { DailyEngagement } from "@/types/dailyEngagement/types"
import { TimeEntry, TimeEntryType } from "@/types/timeEntry/types"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"
import {
    DAY_STATUS_LABELS,
    DAY_STATUS_VARIANTS,
    DAY_TYPE_LABELS,
    DAY_TYPE_VARIANTS,
} from "../../lib/labels"
import { formatBalance, formatMinutes } from "../../lib/time"
import { PunchInline } from "./punch-inline"

interface TimesheetTableProps {
    registerDate: Date
    monthDate: Date
    daysByDate: Map<string, DailyEngagement>
    isLoading?: boolean
    isSaving?: boolean
    canFullDay: boolean
    onAddPunch: (date: string, time: string, type: TimeEntryType) => Promise<boolean>
    onUpdatePunchTime: (punch: TimeEntry, time: string) => void
    onTogglePunchType: (punch: TimeEntry) => void
    onDeletePunch: (punch: TimeEntry) => void
    onFullDay: (date: string) => void
    onEditDayType: (date: string, day?: DailyEngagement) => void
    onSubmitDay: (day: DailyEngagement) => void
}

function statusTooltip(day: DailyEngagement): string | undefined {
    if (!day.approval.byName) return undefined

    if (day.status === "approved") return `Aprovado por ${day.approval.byName}`
    if (day.status === "rejected") return `Rejeitado por ${day.approval.byName}`

    return undefined
}

export function TimesheetTable({
    registerDate,
    monthDate,
    daysByDate,
    isLoading,
    isSaving,
    canFullDay,
    onAddPunch,
    onUpdatePunchTime,
    onTogglePunchType,
    onDeletePunch,
    onFullDay,
    onEditDayType,
    onSubmitDay,
}: TimesheetTableProps) {
    const isMobile = useIsMobile()

    const days = useMemo(() => {
        const startDate = isSameMonth(monthDate, registerDate)
            ? registerDate
            : startOfMonth(monthDate);

        return eachDayOfInterval({
            start: startDate,
            end: endOfMonth(monthDate),
        });
    }, [monthDate, registerDate]);

    const engagements = [...daysByDate.values()]
    const totals = {
        worked: engagements.reduce((sum, day) => sum + day.workedMinutes, 0),
        expected: engagements.reduce((sum, day) => sum + day.expectedMinutes, 0),
        balance: engagements.reduce((sum, day) => sum + day.balanceMinutes, 0),
    }

    return (
        <div
            className={cn(
                "overflow-x-auto rounded-xl border bg-card",
                isLoading && "pointer-events-none opacity-60"
            )}
        >
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead className="w-20">Dia</TableHead>
                        {!isMobile && <TableHead className="w-24">Tipo</TableHead>}
                        <TableHead>Marcações</TableHead>
                        {!isMobile && <TableHead className="w-24 text-right">Trabalhado</TableHead>}
                        {!isMobile && <TableHead className="w-24 text-right">Esperado</TableHead>}
                        <TableHead className="w-20 text-right">Saldo</TableHead>
                        {!isMobile && <TableHead className="w-24">Status</TableHead>}
                        <TableHead className="w-20" />
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {days.map((dayDate) => {
                        const date = format(dayDate, "yyyy-MM-dd")
                        const day = daysByDate.get(date)
                        const weekend = isWeekend(dayDate)

                        return (
                            <TableRow
                                key={date}
                                className={cn(weekend && "bg-muted/40", isToday(dayDate) && "bg-primary/5")}
                            >
                                <TableCell
                                    className={cn(
                                        "whitespace-nowrap font-medium capitalize",
                                        weekend && "text-muted-foreground"
                                    )}
                                >
                                    {format(dayDate, "dd EEEEEE", { locale: ptBR })}
                                </TableCell>

                                {!isMobile && (
                                    <TableCell>
                                        {day ? (
                                            <Badge variant={DAY_TYPE_VARIANTS[day.type]}>
                                                {DAY_TYPE_LABELS[day.type]}
                                            </Badge>
                                        ) : (
                                            <span className="text-muted-foreground">—</span>
                                        )}
                                    </TableCell>
                                )}

                                <TableCell>
                                    <PunchInline
                                        date={date}
                                        day={day}
                                        canFullDay={canFullDay}
                                        isSaving={isSaving}
                                        onAdd={onAddPunch}
                                        onUpdateTime={onUpdatePunchTime}
                                        onToggleType={onTogglePunchType}
                                        onDelete={onDeletePunch}
                                        onFullDay={onFullDay}
                                    />
                                </TableCell>

                                {!isMobile && (
                                    <TableCell className="text-right tabular-nums">
                                        {day ? formatMinutes(day.workedMinutes) : "—"}
                                    </TableCell>
                                )}
                                {!isMobile && (
                                    <TableCell className="text-right tabular-nums">
                                        {day ? formatMinutes(day.expectedMinutes) : "—"}
                                    </TableCell>
                                )}

                                <TableCell
                                    className={cn(
                                        "text-right tabular-nums",
                                        day && day.balanceMinutes < 0 && "text-destructive",
                                        day && day.balanceMinutes > 0 && "text-primary"
                                    )}
                                >
                                    {day ? formatBalance(day.balanceMinutes) : "—"}
                                </TableCell>

                                {!isMobile && (
                                    <TableCell>
                                        {day && (
                                            <Badge
                                                variant={DAY_STATUS_VARIANTS[day.status]}
                                                title={statusTooltip(day)}
                                            >
                                                {DAY_STATUS_LABELS[day.status]}
                                            </Badge>
                                        )}
                                    </TableCell>
                                )}

                                <TableCell>
                                    <div className="flex items-center justify-end gap-0.5">
                                        {day?.status === "draft" && (
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                onClick={() => onSubmitDay(day)}
                                                title="Enviar p/ aprovação"
                                                aria-label="Enviar dia p/ aprovação"
                                            >
                                                <SendIcon className="text-primary" />
                                            </Button>
                                        )}
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            onClick={() => onEditDayType(date, day)}
                                            title="Tipo do dia (folga, feriado, atestado, falta)"
                                            aria-label="Tipo do dia"
                                        >
                                            <CalendarCogIcon />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        )
                    })}

                    <TableRow className="bg-muted/50 font-medium hover:bg-muted/50">
                        <TableCell colSpan={isMobile ? 2 : 3}>Total do mês</TableCell>
                        {!isMobile && (
                            <TableCell className="text-right tabular-nums">
                                {formatMinutes(totals.worked)}
                            </TableCell>
                        )}
                        {!isMobile && (
                            <TableCell className="text-right tabular-nums">
                                {formatMinutes(totals.expected)}
                            </TableCell>
                        )}
                        <TableCell
                            className={cn(
                                "text-right tabular-nums",
                                totals.balance < 0 && "text-destructive",
                                totals.balance > 0 && "text-primary"
                            )}
                        >
                            {formatBalance(totals.balance)}
                        </TableCell>
                        <TableCell colSpan={isMobile ? 1 : 2} />
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    )
}

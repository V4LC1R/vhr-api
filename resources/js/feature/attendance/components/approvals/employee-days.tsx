import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { CheckIcon, TriangleAlertIcon, XIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { DailyEngagement } from "@/types/dailyEngagement/types"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Checkbox } from "@/components/ui/checkbox"
import { ApprovalGroup } from "../../types/approvals"
import { DAY_TYPE_LABELS, DAY_TYPE_VARIANTS } from "../../lib/labels"
import { buildPunchPairs, isBrokenPair } from "../../lib/punch-pairs"
import { formatBalance, punchTimeLocal } from "../../lib/time"

interface EmployeeDaysProps {
    group: ApprovalGroup
    selectedIds: Set<string>
    isBusy?: boolean
    /** Histórico (aprovados/rejeitados): sem seleção nem ações, só consulta. */
    readOnly?: boolean
    onToggle: (dayId: string) => void
    onToggleAll: (group: ApprovalGroup, checked: boolean) => void
    onApprove: (day: DailyEngagement) => void
    onReject: (day: DailyEngagement) => void
}

export function DayPairs({ day }: { day: DailyEngagement }) {
    const { pairs } = buildPunchPairs(day.timeEntries ?? [])

    if (pairs.length === 0) {
        return <span className="text-xs text-muted-foreground">Sem marcações</span>
    }

    return (
        <>
            {pairs.map((pair, index) => {
                const broken = isBrokenPair(pair)
                return (
                    <Badge
                        key={index}
                        variant="outline"
                        className={cn(
                            "gap-1 tabular-nums",
                            broken && "border-destructive/60 text-destructive"
                        )}
                        title={broken ? "Sequência inconsistente (entrada/saída sem par)" : undefined}
                    >
                        {broken && <TriangleAlertIcon className="size-3" />}
                        {pair.entry ? punchTimeLocal(pair.entry.punchedAt) : "??"}
                        {" → "}
                        {pair.exit ? punchTimeLocal(pair.exit.punchedAt) : "??"}
                    </Badge>
                )
            })}
        </>
    )
}

export function EmployeeDays({
    group,
    selectedIds,
    isBusy,
    readOnly,
    onToggle,
    onToggleAll,
    onApprove,
    onReject,
}: EmployeeDaysProps) {
    const selectedInGroup = group.days.filter((day) => selectedIds.has(day.id)).length
    const allSelected = selectedInGroup === group.days.length && group.days.length > 0

    return (
        <div className="flex flex-col gap-1 p-3">
            {!readOnly && (
                <label className="flex w-fit cursor-pointer items-center gap-2 px-1 pb-1 text-xs text-muted-foreground">
                    <Checkbox
                        checked={allSelected}
                        indeterminate={selectedInGroup > 0 && !allSelected}
                        onCheckedChange={(checked) => onToggleAll(group, !!checked)}
                    />
                    Marcar todos os dias
                </label>
            )}

            {group.days.map((day) => (
                <div
                    key={day.id}
                    className={cn(
                        "flex flex-wrap items-center gap-2 rounded-lg border bg-background px-2 py-1.5",
                        selectedIds.has(day.id) && "border-primary/50 bg-primary/5"
                    )}
                >
                    {!readOnly && (
                        <Checkbox
                            checked={selectedIds.has(day.id)}
                            onCheckedChange={() => onToggle(day.id)}
                            aria-label="Marcar dia"
                        />
                    )}

                    <span className="w-24 whitespace-nowrap text-sm font-medium capitalize">
                        {day.date &&
                            format(new Date(`${day.date}T00:00:00`), "dd/MM EEEEEE", { locale: ptBR })}
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

                    {!readOnly && (
                        <div className="flex items-center gap-1">
                            <Button
                                variant="outline"
                                size="sm"
                                className="h-7 gap-1 px-2"
                                disabled={isBusy}
                                onClick={() => onApprove(day)}
                            >
                                <CheckIcon className="size-3.5 text-primary" />
                                Aceitar
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                className="h-7 gap-1 px-2 text-destructive hover:text-destructive"
                                disabled={isBusy}
                                onClick={() => onReject(day)}
                            >
                                <XIcon className="size-3.5" />
                                Rejeitar
                            </Button>
                        </div>
                    )}

                    {day.status === "approved" && day.approval.byName && (
                        <p className="w-full text-xs text-muted-foreground">
                            Aprovado por {day.approval.byName}
                            {day.approval.at &&
                                ` em ${format(new Date(day.approval.at), "dd/MM/yyyy 'às' HH:mm", { locale: ptBR })}`}
                        </p>
                    )}

                    {day.status === "rejected" && (
                        <p className="w-full text-xs text-destructive">
                            Rejeitado{day.approval.byName && ` por ${day.approval.byName}`}
                            {day.note ? ` — motivo: ${day.note}` : " — sem motivo informado"}
                        </p>
                    )}
                </div>
            ))}
        </div>
    )
}

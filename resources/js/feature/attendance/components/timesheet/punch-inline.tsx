
import { CheckIcon, LogInIcon, LogOutIcon, XIcon, ZapIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { DailyEngagement } from "@/types/dailyEngagement/types"
import { TimeEntry, TimeEntryType } from "@/types/timeEntry/types"
import { Button } from "@/components/ui/button"
import { PUNCH_TYPE_LABELS } from "../../lib/labels"
import { punchTimeLocal } from "../../lib/time"
import { useMemo, useState } from "react"

interface PunchInlineProps {
    date: string
    day?: DailyEngagement
    /** Colaborador tem jornada ativa (habilita o "dia completo"). */
    canFullDay: boolean
    /** Vínculo não-CLT: o lançamento rápido vira "Presente" (1 clique = jornada do dia). */
    presenceMode?: boolean
    isSaving?: boolean
    onAdd: (date: string, time: string, type: TimeEntryType) => Promise<boolean>
    onUpdateTime: (punch: TimeEntry, time: string) => void
    onToggleType: (punch: TimeEntry) => void
    onDelete: (punch: TimeEntry) => void
    onFullDay: (date: string) => void
}

const timeInputClass =
    "h-6 w-[72px] rounded-sm border-0 bg-transparent px-0.5 text-xs tabular-nums outline-none focus-visible:ring-2 focus-visible:ring-ring [&::-webkit-calendar-picker-indicator]:hidden"

function blurOnEnter(event: React.KeyboardEvent<HTMLInputElement>) {
    if (event.key === "Enter") event.currentTarget.blur()
}

interface PunchChipProps {
    punch: TimeEntry
    onUpdateTime: (time: string) => void
    onToggleType: () => void
    onDelete: () => void
}

function PunchChip({ punch, onUpdateTime, onToggleType, onDelete }: PunchChipProps) {
    const time = punchTimeLocal(punch.punchedAt)
    const Icon = punch.type === "entry" ? LogInIcon : LogOutIcon

    return (
        <div className="group relative flex items-center gap-0.5 rounded-md border bg-background pl-1">
            <button
                type="button"
                onClick={onToggleType}
                title={`${PUNCH_TYPE_LABELS[punch.type]} — clique pra alternar`}
                aria-label={`${PUNCH_TYPE_LABELS[punch.type]} — alternar tipo`}
                className="flex items-center"
            >
                <Icon
                    className={cn(
                        "size-3",
                        punch.type === "entry" ? "text-primary" : "text-muted-foreground"
                    )}
                />
            </button>
            <input
                type="time"
                // punchedAt na key remonta o input (e o defaultValue) quando o valor muda no back.
                key={punch.punchedAt}
                defaultValue={time}
                onKeyDown={blurOnEnter}
                onBlur={(e) => {
                    if (e.target.value && e.target.value !== time) onUpdateTime(e.target.value)
                }}
                aria-label={`Horário da ${PUNCH_TYPE_LABELS[punch.type].toLowerCase()}`}
                className={timeInputClass}
            />
            {/* Overlay absoluto: não reserva espaço no chip — aparece sobre a ponta vazia do input no hover. */}
            <button
                type="button"
                onClick={onDelete}
                aria-label="Excluir marcação"
                className="absolute inset-y-0 right-0 flex items-center rounded-r-md bg-background px-1 text-muted-foreground opacity-0 transition-opacity hover:text-destructive focus-visible:opacity-100 group-hover:opacity-100"
            >
                <XIcon className="size-3" />
            </button>
        </div>
    )
}

export function PunchInline({
    date,
    day,
    canFullDay,
    presenceMode,
    isSaving,
    onAdd,
    onUpdateTime,
    onToggleType,
    onDelete,
    onFullDay,
}: PunchInlineProps) {
    const [draft, setDraft] = useState("")

    const punches = useMemo(
        () =>
            [...(day?.timeEntries ?? [])].sort((a, b) => a.punchedAt.localeCompare(b.punchedAt)),
        [day?.timeEntries]
    )

    // Alterna entrada/saída pela quantidade de marcações já lançadas no dia.
    const nextType: TimeEntryType = punches.length % 2 === 0 ? "entry" : "exit"

    async function commitDraft() {
        if (!draft) return
        const created = await onAdd(date, draft, nextType)
        if (created) setDraft("")
    }

    return (
        // w-fit: as duas colunas abraçam o conteúdo — pares entrada|saída alinhados sem esticar na célula.
        <div className="grid w-fit grid-cols-2 items-center gap-1">
            {punches.map((punch) => (
                <PunchChip
                    key={punch.id}
                    punch={punch}
                    onUpdateTime={(time) => onUpdateTime(punch, time)}
                    onToggleType={() => onToggleType(punch)}
                    onDelete={() => onDelete(punch)}
                />
            ))}

            <div
                className="flex items-center gap-0.5 rounded-md border border-dashed pl-1"
                title={`Nova marcação (${PUNCH_TYPE_LABELS[nextType].toLowerCase()})`}
            >
                {nextType === "entry" ? (
                    <LogInIcon className="size-3 text-muted-foreground/60" />
                ) : (
                    <LogOutIcon className="size-3 text-muted-foreground/60" />
                )}
                <input
                    type="time"
                    value={draft}
                    disabled={isSaving}
                    onChange={(e) => setDraft(e.target.value)}
                    onKeyDown={(e) => {
                        if (e.key === "Enter") commitDraft()
                    }}
                    onBlur={commitDraft}
                    aria-label={`Nova marcação de ${PUNCH_TYPE_LABELS[nextType].toLowerCase()}`}
                    className={cn(timeInputClass, "text-muted-foreground")}
                />
            </div>

            {canFullDay && (
                <Button
                    variant="outline"
                    size="sm"
                    className="h-6 gap-1 px-2 text-xs"
                    disabled={isSaving}
                    onClick={() => onFullDay(date)}
                    title={
                        punches.length > 0
                            ? "Substitui as marcações do dia pelas da jornada"
                            : presenceMode
                              ? "Marca presença — lança a jornada do dia"
                              : "Lança as marcações da jornada de uma vez"
                    }
                >
                    {presenceMode ? <CheckIcon className="size-3" /> : <ZapIcon className="size-3" />}
                    {presenceMode ? "Presente" : "Completo"}
                </Button>
            )}
        </div>
    )
}

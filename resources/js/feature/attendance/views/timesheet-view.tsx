import { useEffect, useMemo, useState } from "react"
import { format, startOfMonth,parse, isValid } from "date-fns"
import { ptBR } from "date-fns/locale"
import { CalendarClockIcon, Loader2Icon, ZapIcon } from "lucide-react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { ConfirmDialog } from "@/components/confirm-dialog"
import { DailyEngagement } from "@/types/dailyEngagement/types"
import { Employee } from "@/types/employee/types"
import { TimeEntry, TimeEntryType } from "@/types/timeEntry/types"
import { useListDailyEngagements } from "../hooks/useListDailyEngagements"
import { useCreateTimeEntry } from "../hooks/useCreateTimeEntry"
import { useUpdateTimeEntry } from "../hooks/useUpdateTimeEntry"
import { useDeleteTimeEntry } from "../hooks/useDeleteTimeEntry"
import { useBatchCreateTimeEntries, BatchTimeEntry } from "../hooks/useBatchCreateTimeEntries"
import { useUpsertDayException } from "../hooks/useUpsertDayException"
import { useDayActions } from "../hooks/useDayActions"
import { toPunchedAtISO } from "../lib/time"
import { ExceptionPayload } from "../types/schemas"
import { EmployeeSelect } from "../components/employee-select"
import { MonthPicker } from "../components/month-picker"
import { ExceptionDialog } from "../components/exception-dialog"
import { TimesheetTable } from "../components/timesheet/timesheet-table"

type ExceptionDialogState = {
    open: boolean
    date: string
    day: DailyEngagement | null
}

/** Horários da jornada (HH:mm) na ordem entrada → intervalo → volta → saída. */
function workloadTimes(employee: Employee): string[] {
    const workload = employee.activeEmployment?.workload
    if (!workload) return []

    return [
        workload.entryTime,
        workload.interval.startAt,
        workload.interval.endAt,
        workload.leftTime,
    ]
        .filter((time): time is string => !!time)
        .map((time) => time.slice(0, 5))
}

export function TimesheetView() {
    const [employee, setEmployee] = useState<Employee | null>(null)
    const [monthDate, setMonthDate] = useState<Date>(() => startOfMonth(new Date()))
    const [exceptionDialog, setExceptionDialog] = useState<ExceptionDialogState>({
        open: false,
        date: "",
        day: null,
    })
    const [fullDayToReplace, setFullDayToReplace] = useState<string | null>(null)

    const employeeId = employee?.id ?? null

    // O mês inteiro vem de uma vez (máx. 31 dias) — sem paginação nesta tela.
    const { list, data, isLoadingDays } = useListDailyEngagements({ per_page: 40, sort: "date" })
    const { create: createPunch, isCreatingTimeEntry } = useCreateTimeEntry()
    const { update: updatePunch, isUpdatingTimeEntry } = useUpdateTimeEntry()
    const { remove: deletePunch, isDeletingTimeEntry } = useDeleteTimeEntry()
    const { createBatch, isCreatingBatch } = useBatchCreateTimeEntries()
    const { upsert: upsertException, isUpsertingException } = useUpsertDayException()
    const { submit: submitDay } = useDayActions()

    const isSaving =
        isCreatingTimeEntry || isUpdatingTimeEntry || isDeletingTimeEntry || isCreatingBatch

    async function refresh() {
        if (!employeeId) return
        await list({
            filter: { employeeId, month: format(monthDate, "yyyy-MM") },
            page: 1,
        })
    }

    useEffect(() => {
        refresh()
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [employeeId, monthDate])

    const daysByDate = useMemo(
        () =>
            new Map(
                (data ?? [])
                    .filter((day) => day.date)
                    .map((day) => [day.date as string, day])
            ),
        [data]
    )

    async function handleAddPunch(date: string, time: string, type: TimeEntryType) {
        if (!employeeId) return false

        try {
            await createPunch({ employeeId, punchedAt: toPunchedAtISO(date, time), type })
            await refresh()
            return true
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível lançar a marcação."))
            return false
        }
    }

    async function handleUpdatePunchTime(punch: TimeEntry, time: string) {
        const date = format(new Date(punch.punchedAt), "yyyy-MM-dd")

        try {
            await updatePunch(punch.id, { punchedAt: toPunchedAtISO(date, time) })
            await refresh()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível atualizar a marcação."))
        }
    }

    async function handleTogglePunchType(punch: TimeEntry) {
        try {
            await updatePunch(punch.id, { type: punch.type === "entry" ? "exit" : "entry" })
            await refresh()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível alterar o tipo."))
        }
    }

    async function handleDeletePunch(punch: TimeEntry) {
        try {
            await deletePunch(punch.id)
            await refresh()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível excluir a marcação."))
        }
    }

    function handleFullDay(date: string) {
        const hasPunches = (daysByDate.get(date)?.timeEntries?.length ?? 0) > 0

        // Dia já tem marcações: o full substitui — confirma antes.
        if (hasPunches) {
            setFullDayToReplace(date)
            return
        }

        launchFullDay(date)
    }

    async function launchFullDay(date: string) {
        if (!employee || !employeeId) return

        const times = workloadTimes(employee)
        if (times.length < 2) {
            toast.error("Colaborador sem jornada ativa configurada.")
            return
        }

        const entries: BatchTimeEntry[] = times.map((time, index) => ({
            punchedAt: toPunchedAtISO(date, time),
            type: index % 2 === 0 ? "entry" : "exit",
        }))

        try {
            await createBatch({ employeeId, entries, replace: true })
            toast.success(`Dia completo lançado (${times.join(" · ")}).`)
            setFullDayToReplace(null)
            await refresh()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível lançar o dia completo."))
            setFullDayToReplace(null)
        }
    }

    async function handleExceptionSubmit(values: ExceptionPayload) {
        if (!employeeId) return

        try {
            await upsertException({
                employeeId,
                date: exceptionDialog.date,
                type: values.type,
                note: values.note,
            })
            toast.success("Tipo do dia atualizado!")
            setExceptionDialog((state) => ({ ...state, open: false }))
            await refresh()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível atualizar o dia."))
        }
    }

    async function handleSubmitDay(day: DailyEngagement) {
        try {
            await submitDay(day.id)
            toast.success("Dia enviado para aprovação!")
            await refresh()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível enviar o dia."))
        }
    }

    const registerDate = useMemo(()=>{

        if(!employee?.activeEmployment?.registerAt) return new Date()

        const date = parse(
            employee.activeEmployment.registerAt,
            'yyyy-MM-dd HH:mm:ss',
            new Date()
        );

        if(!isValid(date)) return new Date()

        return date

    },[employee?.activeEmployment])

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <EmployeeSelect value={employeeId} onChange={setEmployee} />
                <MonthPicker value={monthDate} onChange={setMonthDate} disabled={!employeeId} />
            </div>

            {!employeeId ? (
                <div className="flex flex-1 flex-col items-center justify-center gap-3 rounded-xl border bg-card py-20 text-muted-foreground">
                    <CalendarClockIcon className="size-8" />
                    <span>Selecione um colaborador pra lançar o ponto.</span>
                </div>
            ) : isLoadingDays && !data ? (
                <div className="flex flex-1 items-center justify-center rounded-xl border bg-card py-20">
                    <Loader2Icon className="size-6 animate-spin text-muted-foreground" />
                </div>
            ) : (
                <TimesheetTable
                    registerDate={registerDate}
                    monthDate={monthDate}
                    daysByDate={daysByDate}
                    isLoading={isLoadingDays}
                    isSaving={isSaving}
                    canFullDay={!!employee?.activeEmployment?.workload}
                    onAddPunch={handleAddPunch}
                    onUpdatePunchTime={handleUpdatePunchTime}
                    onTogglePunchType={handleTogglePunchType}
                    onDeletePunch={handleDeletePunch}
                    onFullDay={handleFullDay}
                    onEditDayType={(date, day) =>
                        setExceptionDialog({ open: true, date, day: day ?? null })
                    }
                    onSubmitDay={handleSubmitDay}
                />
            )}

            <ConfirmDialog
                open={!!fullDayToReplace}
                onOpenChange={(open) => !open && setFullDayToReplace(null)}
                title="Lançar dia completo"
                description="O dia já tem marcações — elas serão substituídas pelos horários da jornada."
                confirmLabel="Substituir"
                confirmIcon={ZapIcon}
                isLoading={isCreatingBatch}
                onConfirm={() => fullDayToReplace && launchFullDay(fullDayToReplace)}
            />

            <ExceptionDialog
                open={exceptionDialog.open}
                onOpenChange={(open) => setExceptionDialog((state) => ({ ...state, open }))}
                description={
                    exceptionDialog.date
                        ? format(new Date(`${exceptionDialog.date}T00:00:00`), "EEEE, dd 'de' MMMM", {
                              locale: ptBR,
                          })
                        : undefined
                }
                defaultValues={
                    exceptionDialog.day
                        ? { type: exceptionDialog.day.type, note: exceptionDialog.day.note ?? "" }
                        : undefined
                }
                isSubmitting={isUpsertingException}
                onSubmit={handleExceptionSubmit}
            />
        </div>
    )
}

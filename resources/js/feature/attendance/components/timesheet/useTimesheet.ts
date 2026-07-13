import { useEffect, useMemo, useState } from "react"
import { format } from "date-fns"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { DailyEngagement, DailyEngagementType } from "@/types/dailyEngagement/types"
import { Employee } from "@/types/employee/types"
import { TimeEntry, TimeEntryType } from "@/types/timeEntry/types"
import { useListDailyEngagements } from "../../hooks/useListDailyEngagements"
import { useCreateTimeEntry } from "../../hooks/useCreateTimeEntry"
import { useUpdateTimeEntry } from "../../hooks/useUpdateTimeEntry"
import { useDeleteTimeEntry } from "../../hooks/useDeleteTimeEntry"
import { useBatchCreateTimeEntries, BatchTimeEntry } from "../../hooks/useBatchCreateTimeEntries"
import { useUpsertDayException } from "../../hooks/useUpsertDayException"
import { useDayActions } from "../../hooks/useDayActions"
import { useEmploymentPeriod } from "../../hooks/useEmploymentPeriod"
import { toPunchedAtISO } from "../../lib/time"

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

/**
 * Toda a lógica da folha de ponto (dias do mês, marcações, dia completo e
 * envio pra aprovação) — a view só liga o retorno na TimesheetTable.
 */
export function useTimesheet(employee: Employee | null, monthDate: Date) {
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
    const { registerDate, leftDate } = useEmploymentPeriod(employee?.activeEmployment)

    const isSaving =
        isCreatingTimeEntry ||
        isUpdatingTimeEntry ||
        isDeletingTimeEntry ||
        isCreatingBatch ||
        isUpsertingException

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

    /** Troca o tipo do dia direto pelo select da coluna — sem mexer na observação. */
    async function handleChangeDayType(date: string, type: DailyEngagementType) {
        if (!employeeId) return

        try {
            await upsertException({ employeeId, date, type })
            toast.success("Tipo do dia atualizado!")
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

    return {
        // dados do mês
        daysByDate,
        isLoadingDays,
        isInitialLoading: isLoadingDays && !data,
        refresh,
        // vínculo
        registerDate,
        leftDate,
        canFullDay: !!employee?.activeEmployment?.workload,
        // mutações
        isSaving,
        addPunch: handleAddPunch,
        updatePunchTime: handleUpdatePunchTime,
        togglePunchType: handleTogglePunchType,
        deletePunch: handleDeletePunch,
        fullDay: handleFullDay,
        submitDay: handleSubmitDay,
        changeDayType: handleChangeDayType,
        // confirmação de substituição do dia completo
        isCreatingBatch,
        fullDayToReplace,
        confirmFullDayReplace: () => fullDayToReplace && launchFullDay(fullDayToReplace),
        cancelFullDayReplace: () => setFullDayToReplace(null),
    }
}

import { useEffect, useMemo, useState } from "react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { DailyEngagementStatus } from "@/types/dailyEngagement/types"
import {
    DailyEngagementFilters,
    useListDailyEngagements,
} from "../../hooks/useListDailyEngagements"
import { useBatchDayActions } from "../../hooks/useBatchDayActions"
import { ApprovalGroup } from "../../types/approvals"
import { buildPunchPairs } from "../../lib/punch-pairs"

/** Abas da tela: fila pendente + histórico do que foi aprovado/rejeitado. */
export type ApprovalStatusTab = Extract<
    DailyEngagementStatus,
    "pending" | "approved" | "rejected"
>

/**
 * Toda a lógica da fila de aprovações (grupos por colaborador, abas de status,
 * busca por nome, seleção e os fluxos de aprovar/rejeitar em lote) — a view só
 * liga o retorno nos componentes.
 */
export function useApprovals() {
    const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set())
    const [approveIds, setApproveIds] = useState<string[] | null>(null)
    const [rejectIds, setRejectIds] = useState<string[] | null>(null)
    const [status, setStatus] = useState<ApprovalStatusTab>("pending")
    const [searchInput, setSearchInput] = useState("")
    const [search, setSearch] = useState("")

    // Agrupado por colaborador — traz a fila inteira de uma vez (sem paginação).
    const { list, isLoadingDays, data, total } = useListDailyEngagements({
        per_page: 500,
        sort: "date",
    })

    const { approveBatch, isApprovingBatch, rejectBatch, isRejectingBatch } = useBatchDayActions()
    const isBusy = isApprovingBatch || isRejectingBatch

    // Debounce da busca — o fetch de verdade reage a `search`.
    useEffect(() => {
        const handle = setTimeout(() => setSearch(searchInput.trim()), 300)
        return () => clearTimeout(handle)
    }, [searchInput])

    function currentFilter(): DailyEngagementFilters {
        return { status, ...(search ? { employeeName: search } : {}) }
    }

    useEffect(() => {
        setSelectedIds(new Set())
        list({ filter: currentFilter(), page: 1 })
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [status, search])

    const groups = useMemo<ApprovalGroup[]>(() => {
        const byEmployee = new Map<string, ApprovalGroup>()

        for (const day of data ?? []) {
            const group = byEmployee.get(day.employeeId) ?? {
                employeeId: day.employeeId,
                name:
                    day.employee?.person?.name ??
                    `Nº ${day.employee?.registerNumber ?? "—"}`,
                days: [],
                workedMinutes: 0,
                expectedMinutes: 0,
                balanceMinutes: 0,
                hasAnomaly: false,
            }

            group.days.push(day)
            group.workedMinutes += day.workedMinutes
            group.expectedMinutes += day.expectedMinutes
            group.balanceMinutes += day.balanceMinutes
            group.hasAnomaly ||= buildPunchPairs(day.timeEntries ?? []).hasAnomaly

            byEmployee.set(day.employeeId, group)
        }

        return [...byEmployee.values()].sort((a, b) => a.name.localeCompare(b.name))
    }, [data])

    async function refresh() {
        const response = await list({ filter: currentFilter(), page: 1 })
        // Tira da seleção o que saiu da fila (aprovado/rejeitado).
        const stillPending = new Set((response?.data ?? []).map((day) => day.id))
        setSelectedIds((prev) => new Set([...prev].filter((id) => stillPending.has(id))))
    }

    function toggle(dayId: string) {
        setSelectedIds((prev) => {
            const next = new Set(prev)
            if (next.has(dayId)) next.delete(dayId)
            else next.add(dayId)
            return next
        })
    }

    function toggleAll(group: ApprovalGroup, checked: boolean) {
        setSelectedIds((prev) => {
            const next = new Set(prev)
            for (const day of group.days) {
                if (checked) next.add(day.id)
                else next.delete(day.id)
            }
            return next
        })
    }

    async function approveNow(ids: string[]) {
        try {
            const result = await approveBatch(ids)
            toast.success(
                result.skipped > 0
                    ? `${result.approved} dia(s) aprovado(s), ${result.skipped} pulado(s).`
                    : `${result.approved} dia(s) aprovado(s)!`
            )
            setApproveIds(null)
            await refresh()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível aprovar."))
            setApproveIds(null)
        }
    }

    async function confirmReject(note: string) {
        if (!rejectIds || !note) return

        try {
            const result = await rejectBatch(rejectIds, note)
            toast.success(`${result.rejected} dia(s) rejeitado(s).`)
            setRejectIds(null)
            await refresh()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível rejeitar."))
        }
    }

    return {
        // fila
        groups,
        total,
        isLoadingDays,
        isBusy,
        // abas + busca
        status,
        setStatus,
        /** Só a fila pendente permite selecionar/aprovar/rejeitar. */
        isReadOnly: status !== "pending",
        search: searchInput,
        setSearch: setSearchInput,
        // seleção
        selectedIds,
        toggle,
        toggleAll,
        clearSelection: () => setSelectedIds(new Set()),
        // aprovação
        approveIds,
        approveNow,
        openApprove: (ids: string[]) => setApproveIds(ids),
        cancelApprove: () => setApproveIds(null),
        confirmApprove: () => approveIds && approveNow(approveIds),
        isApprovingBatch,
        // rejeição
        rejectIds,
        openReject: (ids: string[]) => setRejectIds(ids),
        cancelReject: () => setRejectIds(null),
        confirmReject,
        isRejectingBatch,
    }
}

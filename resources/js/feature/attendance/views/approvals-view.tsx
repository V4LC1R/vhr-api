import { useEffect, useMemo, useState } from "react"
import { CheckIcon, XIcon } from "lucide-react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { useIsMobile } from "@/hooks/use-mobile"
import { DailyEngagement } from "@/types/dailyEngagement/types"
import { ConfirmDialog } from "@/components/confirm-dialog"
import { DataTable } from "@/components/table/data-table"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Field, FieldLabel } from "@/components/ui/field"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"
import { useListDailyEngagements } from "../hooks/useListDailyEngagements"
import { useBatchDayActions } from "../hooks/useBatchDayActions"
import { ApprovalGroup } from "../types/approvals"
import { buildPunchPairs } from "../lib/punch-pairs"
import { getApprovalColumns } from "../components/approvals/columns"
import { EmployeeDays } from "../components/approvals/employee-days"

export function ApprovalsView() {
    const isMobile = useIsMobile()
    const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set())
    const [approveIds, setApproveIds] = useState<string[] | null>(null)
    const [rejectIds, setRejectIds] = useState<string[] | null>(null)
    const [rejectNote, setRejectNote] = useState("")

    // Agrupado por colaborador — traz a fila inteira de uma vez (sem paginação).
    const { list, isLoadingDays, data, total } = useListDailyEngagements({
        per_page: 500,
        sort: "date",
        filter: { status: "pending" },
    })

    const { approveBatch, isApprovingBatch, rejectBatch, isRejectingBatch } = useBatchDayActions()
    const isBusy = isApprovingBatch || isRejectingBatch

    useEffect(() => {
        list()
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [])

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
        const response = await list()
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

    async function confirmReject() {
        if (!rejectIds || !rejectNote.trim()) return

        try {
            const result = await rejectBatch(rejectIds, rejectNote.trim())
            toast.success(`${result.rejected} dia(s) rejeitado(s).`)
            setRejectIds(null)
            setRejectNote("")
            await refresh()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível rejeitar."))
        }
    }

    function openReject(ids: string[]) {
        setRejectNote("")
        setRejectIds(ids)
    }

    const columns = useMemo(() => getApprovalColumns(isMobile), [isMobile])

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            {selectedIds.size > 0 && (
                <div className="flex flex-wrap items-center justify-between gap-2 rounded-xl border bg-card px-3 py-2">
                    <span className="text-sm">
                        <span className="font-medium">{selectedIds.size}</span>{" "}
                        {selectedIds.size === 1 ? "dia marcado" : "dias marcados"}
                    </span>
                    <div className="flex items-center gap-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setSelectedIds(new Set())}
                        >
                            Limpar
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            className="text-destructive hover:text-destructive"
                            disabled={isBusy}
                            onClick={() => openReject([...selectedIds])}
                        >
                            <XIcon />
                            Rejeitar marcados
                        </Button>
                        <Button
                            size="sm"
                            disabled={isBusy}
                            onClick={() => setApproveIds([...selectedIds])}
                        >
                            <CheckIcon />
                            Aceitar marcados
                        </Button>
                    </div>
                </div>
            )}

            <DataTable
                data={groups}
                columns={columns}
                isLoading={isLoadingDays}
                emptyMessage="Nenhum dia pendente de aprovação."
                renderExpandedRow={(row) => (
                    <EmployeeDays
                        group={row.original}
                        selectedIds={selectedIds}
                        isBusy={isBusy}
                        onToggle={toggle}
                        onToggleAll={toggleAll}
                        onApprove={(day: DailyEngagement) => approveNow([day.id])}
                        onReject={(day: DailyEngagement) => openReject([day.id])}
                    />
                )}
                footer={
                    <span className="text-sm text-muted-foreground">
                        {total ?? 0} {total === 1 ? "dia pendente" : "dias pendentes"} ·{" "}
                        {groups.length} {groups.length === 1 ? "colaborador" : "colaboradores"}
                    </span>
                }
            />

            <ConfirmDialog
                open={!!approveIds}
                onOpenChange={(open) => !open && setApproveIds(null)}
                title="Aprovar dias marcados"
                description={`Confirma a aprovação de ${approveIds?.length ?? 0} dia(s)?`}
                confirmLabel="Aprovar"
                confirmIcon={CheckIcon}
                isLoading={isApprovingBatch}
                onConfirm={() => approveIds && approveNow(approveIds)}
            />

            <Dialog open={!!rejectIds} onOpenChange={(open) => !open && setRejectIds(null)}>
                <DialogContent className="sm:max-w-sm">
                    <DialogHeader>
                        <DialogTitle>Rejeitar {rejectIds && rejectIds.length > 1 ? `${rejectIds.length} dias` : "dia"}</DialogTitle>
                        <DialogDescription>
                            Os dias rejeitados voltam pra correção e reenvio.
                        </DialogDescription>
                    </DialogHeader>

                    <Field>
                        <FieldLabel htmlFor="reject-note">Motivo</FieldLabel>
                        <Input
                            id="reject-note"
                            value={rejectNote}
                            onChange={(e) => setRejectNote(e.target.value)}
                            placeholder="Explique o que precisa ser corrigido"
                            maxLength={255}
                        />
                    </Field>

                    <DialogFooter>
                        <DialogClose render={<Button variant="outline" />}>Cancelar</DialogClose>
                        <Button
                            variant="destructive"
                            disabled={isRejectingBatch || !rejectNote.trim()}
                            onClick={confirmReject}
                        >
                            <XIcon />
                            {isRejectingBatch ? "Rejeitando..." : "Rejeitar"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    )
}

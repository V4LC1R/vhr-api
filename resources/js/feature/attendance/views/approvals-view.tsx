import { useMemo } from "react"
import { CheckIcon, SearchIcon } from "lucide-react"

import { useIsMobile } from "@/hooks/use-mobile"
import { DailyEngagement } from "@/types/dailyEngagement/types"
import { ConfirmDialog } from "@/components/confirm-dialog"
import { DataTable } from "@/components/table/data-table"
import { Input } from "@/components/ui/input"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { getApprovalColumns } from "../components/approvals/columns"
import { EmployeeDays } from "../components/approvals/employee-days"
import { RejectDialog } from "../components/approvals/reject-dialog"
import { SelectionBar } from "../components/approvals/selection-bar"
import {
    ApprovalKindTab,
    ApprovalStatusTab,
    useApprovals,
} from "../components/approvals/useApprovals"

const TAB_LABELS: Record<ApprovalStatusTab, string> = {
    pending: "Pendentes",
    approved: "Aprovados",
    rejected: "Rejeitados",
}

const KIND_TAB_LABELS: Record<ApprovalKindTab, string> = {
    clt: "CLTs",
    temps: "Temporários",
}

const DAY_COUNT_LABELS: Record<ApprovalStatusTab, [string, string]> = {
    pending: ["dia pendente", "dias pendentes"],
    approved: ["dia aprovado", "dias aprovados"],
    rejected: ["dia rejeitado", "dias rejeitados"],
}

export function ApprovalsView() {
    const isMobile = useIsMobile()
    const approvals = useApprovals()

    const columns = useMemo(
        () => getApprovalColumns(isMobile, TAB_LABELS[approvals.status]),
        [isMobile, approvals.status]
    )

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <div className="flex flex-wrap items-center gap-2">
                    <Tabs
                        value={approvals.kindTab}
                        onValueChange={(value) => approvals.setKindTab(value as ApprovalKindTab)}
                    >
                        <TabsList>
                            {(Object.keys(KIND_TAB_LABELS) as ApprovalKindTab[]).map((tab) => (
                                <TabsTrigger key={tab} value={tab}>
                                    {KIND_TAB_LABELS[tab]}
                                </TabsTrigger>
                            ))}
                        </TabsList>
                    </Tabs>

                    <Tabs
                        value={approvals.status}
                        onValueChange={(value) => approvals.setStatus(value as ApprovalStatusTab)}
                    >
                        <TabsList>
                            {(Object.keys(TAB_LABELS) as ApprovalStatusTab[]).map((tab) => (
                                <TabsTrigger key={tab} value={tab}>
                                    {TAB_LABELS[tab]}
                                </TabsTrigger>
                            ))}
                        </TabsList>
                    </Tabs>
                </div>

                <div className="relative w-full sm:w-64">
                    <SearchIcon className="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        value={approvals.search}
                        onChange={(e) => approvals.setSearch(e.target.value)}
                        placeholder="Buscar por nome..."
                        className="pl-8"
                    />
                </div>
            </div>

            <SelectionBar
                count={approvals.selectedIds.size}
                isBusy={approvals.isBusy}
                onClear={approvals.clearSelection}
                onRejectSelected={() => approvals.openReject([...approvals.selectedIds])}
                onApproveSelected={() => approvals.openApprove([...approvals.selectedIds])}
            />

            <DataTable
                data={approvals.groups}
                columns={columns}
                isLoading={approvals.isLoadingDays}
                emptyMessage={`Nenhum ${DAY_COUNT_LABELS[approvals.status][0]}${approvals.search ? " pra essa busca" : ""}.`}
                renderExpandedRow={(row) => (
                    <EmployeeDays
                        group={row.original}
                        selectedIds={approvals.selectedIds}
                        isBusy={approvals.isBusy}
                        readOnly={approvals.isReadOnly}
                        onToggle={approvals.toggle}
                        onToggleAll={approvals.toggleAll}
                        onApprove={(day: DailyEngagement) => approvals.approveNow([day.id])}
                        onReject={(day: DailyEngagement) => approvals.openReject([day.id])}
                    />
                )}
                footer={
                    <span className="text-sm text-muted-foreground">
                        {approvals.total ?? 0}{" "}
                        {DAY_COUNT_LABELS[approvals.status][approvals.total === 1 ? 0 : 1]} ·{" "}
                        {approvals.groups.length}{" "}
                        {approvals.groups.length === 1 ? "colaborador" : "colaboradores"}
                    </span>
                }
            />

            <ConfirmDialog
                open={!!approvals.approveIds}
                onOpenChange={(open) => !open && approvals.cancelApprove()}
                title="Aprovar dias marcados"
                description={`Confirma a aprovação de ${approvals.approveIds?.length ?? 0} dia(s)?`}
                confirmLabel="Aprovar"
                confirmIcon={CheckIcon}
                isLoading={approvals.isApprovingBatch}
                onConfirm={approvals.confirmApprove}
            />

            <RejectDialog
                open={!!approvals.rejectIds}
                count={approvals.rejectIds?.length ?? 0}
                isRejecting={approvals.isRejectingBatch}
                onOpenChange={(open) => !open && approvals.cancelReject()}
                onConfirm={approvals.confirmReject}
            />
        </div>
    )
}

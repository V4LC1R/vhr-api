import { useHttp } from "@inertiajs/react";

type BatchApproveResult = { approved: number; skipped: number };
type BatchRejectResult = { rejected: number; skipped: number };

/** Aprovação/rejeição em lote — só os dias PENDENTES da seleção são afetados. */
export function useBatchDayActions() {
    const approveHttp = useHttp<{ ids: string[] }, BatchApproveResult>();
    const rejectHttp = useHttp<{ ids: string[]; note?: string }, BatchRejectResult>();

    async function approveBatch(ids: string[]) {
        approveHttp.setData({ ids });
        return await approveHttp.post("/api/v1/daily-engagements/approve-batch");
    }

    async function rejectBatch(ids: string[], note?: string) {
        rejectHttp.setData({ ids, note: note || undefined });
        return await rejectHttp.post("/api/v1/daily-engagements/reject-batch");
    }

    return {
        approveBatch,
        isApprovingBatch: approveHttp.processing,
        rejectBatch,
        isRejectingBatch: rejectHttp.processing,
    };
}

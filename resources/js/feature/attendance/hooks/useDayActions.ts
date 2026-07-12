import { useHttp } from "@inertiajs/react";
import { DailyEngagement } from "@/types/dailyEngagement/types";

/** Transições de status do dia: enviar p/ aprovação, aprovar e rejeitar. */
export function useDayActions() {
    const submitHttp = useHttp<Record<string, never>, DailyEngagement>();
    const approveHttp = useHttp<Record<string, never>, DailyEngagement>();
    const rejectHttp = useHttp<{ note?: string | null }, DailyEngagement>();

    async function submit(id: string) {
        return await submitHttp.post(`/api/v1/daily-engagements/${id}/submit`);
    }

    async function approve(id: string) {
        return await approveHttp.post(`/api/v1/daily-engagements/${id}/approve`);
    }

    async function reject(id: string, note?: string) {
        rejectHttp.setData({ note: note || undefined });
        return await rejectHttp.post(`/api/v1/daily-engagements/${id}/reject`);
    }

    return {
        submit,
        isSubmittingDay: submitHttp.processing,
        approve,
        isApprovingDay: approveHttp.processing,
        reject,
        isRejectingDay: rejectHttp.processing,
    };
}

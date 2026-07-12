import { useHttp } from "@inertiajs/react";
import { DailyEngagement, DailyEngagementType } from "@/types/dailyEngagement/types";

export type UpsertDayExceptionPayload = {
    employeeId: string;
    date: string; // YYYY-MM-DD
    type: DailyEngagementType;
    note?: string | null;
};

/** Define o tipo do dia por funcionário+data — cria o dia como rascunho se não existir. */
export function useUpsertDayException() {
    const { post, setData, processing } = useHttp<UpsertDayExceptionPayload, DailyEngagement>();

    async function upsert(payload: UpsertDayExceptionPayload) {
        setData(payload);
        return await post("/api/v1/daily-engagements");
    }

    return { upsert, isUpsertingException: processing };
}

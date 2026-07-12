import { useHttp } from "@inertiajs/react";
import { DailyEngagement } from "@/types/dailyEngagement/types";
import { TimeEntryType } from "@/types/timeEntry/types";

export type BatchTimeEntry = {
    punchedAt: string; // ISO com offset do fuso
    type: TimeEntryType;
    note?: string | null;
};

export type BatchCreateTimeEntriesPayload = {
    employeeId: string;
    entries: BatchTimeEntry[];
    /** Substitui as marcações existentes dos dias afetados pelas do lote. */
    replace?: boolean;
};

/** Lança várias marcações num request só (ex.: "dia completo" a partir da jornada). */
export function useBatchCreateTimeEntries() {
    const { post, setData, processing } = useHttp<BatchCreateTimeEntriesPayload, DailyEngagement[]>();

    async function createBatch(payload: BatchCreateTimeEntriesPayload) {
        setData(payload);
        return await post("/api/v1/time-entries/batch");
    }

    return { createBatch, isCreatingBatch: processing };
}

import { useHttp } from "@inertiajs/react";
import { TimeEntry, TimeEntryType } from "@/types/timeEntry/types";

export type CreateTimeEntryPayload = {
    employeeId: string;
    punchedAt: string; // ISO com offset do fuso
    type: TimeEntryType;
    note?: string | null;
};

export function useCreateTimeEntry() {
    const { post, setData, processing } = useHttp<CreateTimeEntryPayload, TimeEntry>();

    async function create(payload: CreateTimeEntryPayload) {
        setData(payload);
        return await post("/api/v1/time-entries");
    }

    return { create, isCreatingTimeEntry: processing };
}

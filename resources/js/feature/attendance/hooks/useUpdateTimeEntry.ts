import { useHttp } from "@inertiajs/react";
import { TimeEntry, TimeEntryType } from "@/types/timeEntry/types";

export type UpdateTimeEntryPayload = {
    punchedAt?: string; // ISO com offset do fuso
    type?: TimeEntryType;
    note?: string | null;
};

export function useUpdateTimeEntry() {
    const { put, setData, processing } = useHttp<UpdateTimeEntryPayload, TimeEntry>();

    async function update(id: string, payload: UpdateTimeEntryPayload) {
        setData(payload);
        return await put(`/api/v1/time-entries/${id}`);
    }

    return { update, isUpdatingTimeEntry: processing };
}

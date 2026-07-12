import { useHttp } from "@inertiajs/react";
import { WorkloadPayload } from "../types/schemas";
import { mapToApi } from "../types/mappers";
import { Workload } from "../types/types";

export function useUpdateWorkload() {
    const { put, setData, processing } = useHttp<WorkloadPayload, Workload>();

    async function update(id: string, payload: WorkloadPayload) {
        setData(mapToApi(payload));
        return await put(`/api/v1/workloads/${id}`);
    }

    return { update, isUpdatingWorkload: processing };
}

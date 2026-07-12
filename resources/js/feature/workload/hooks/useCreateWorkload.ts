import { useHttp } from "@inertiajs/react";
import { WorkloadPayload } from "../types/schemas";
import { mapToApi } from "../types/mappers";
import { Workload } from "../types/types";

export function useCreateWorkload() {
    const { post, setData, processing } = useHttp<WorkloadPayload, Workload>();

    async function create(payload: WorkloadPayload) {
        setData(mapToApi(payload));
        return await post("/api/v1/workloads");
    }

    return { create, isCreatingWorkload: processing };
}

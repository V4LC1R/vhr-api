import { useHttp } from "@inertiajs/react";
import { PaginatedResponse } from "@/types/paginate-requests";
import { Workload } from "../types/types";

type WorkloadListParams = {
    page?: number;
    per_page?: number;
};

type WorkloadResponsePaginated = PaginatedResponse<Workload>;

export function useListWorkload(defaultParams: WorkloadListParams = {}) {
    const { get, processing, setData, response } = useHttp<WorkloadListParams, WorkloadResponsePaginated>();

    async function list(params?: WorkloadListParams) {
        setData({ ...defaultParams, ...params });
        return await get("/api/v1/workloads");
    }

    return { list, isLoadingWorkloads: processing, ...response };
}

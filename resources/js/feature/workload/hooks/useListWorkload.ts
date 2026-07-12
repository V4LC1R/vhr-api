import { useHttp } from "@inertiajs/react";
import { PaginatedResponse } from "@/types/paginate-requests";
import { Workload } from "../types/types";

type WorkloadListParams = {
    page?: number;
    per_page?: number;
};

type WorkloadResponsePaginated = PaginatedResponse<Workload>;

export function useListWorkload(defaultParams: WorkloadListParams = {}) {
    const { get, processing, setData, data, response } = useHttp<WorkloadListParams, WorkloadResponsePaginated>();

    async function list(params?: WorkloadListParams) {
        setData({ ...defaultParams, ...params });
        return await get("/api/v1/workloads");
    }

    async function nextPage() {
        if (processing || !response || response.current_page >= response.last_page) {
            return;
        }

        return await list({ ...data, page: response.current_page + 1 });
    }

    async function prevPage() {
        if (processing || !response || response.current_page <= 1) {
            return;
        }

        return await list({ ...data, page: response.current_page - 1 });
    }

    return { list, nextPage, prevPage, isLoadingWorkloads: processing, ...response };
}

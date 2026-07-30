import { useQuery } from "@tanstack/react-query";
import { useHttp } from "@inertiajs/react";
import { PaginatedResponse } from "@/types/paginate-requests";
import { Workload } from "../types/types";

type WorkloadListParams = {
    page?: number;
    per_page?: number;
};

type WorkloadResponsePaginated = PaginatedResponse<Workload>;

export function useListWorkload(params: WorkloadListParams = {}) {
    const { get, setData } = useHttp<WorkloadListParams, WorkloadResponsePaginated>();

    const { data: response, isLoading: isLoadingWorkloads } = useQuery({
        queryKey: ["workloads", "list", params],
        queryFn: () => {
            setData(params);
            return get("/api/v1/workloads");
        },
        placeholderData: (previous) => previous,
    });

    return { isLoadingWorkloads, ...response };
}

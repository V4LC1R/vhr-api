import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useHttp } from "@inertiajs/react";
import { WorkloadPayload } from "../types/schemas";
import { mapToApi } from "../types/mappers";
import { Workload } from "../types/types";

export function useCreateWorkload() {
    const { post, setData } = useHttp<WorkloadPayload, Workload>();
    const queryClient = useQueryClient();

    const mutation = useMutation({
        mutationFn: (payload: WorkloadPayload) => {
            setData(mapToApi(payload));
            return post("/api/v1/workloads");
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["workloads"] });
        },
    });

    return { create: mutation.mutateAsync, isCreatingWorkload: mutation.isPending, response: mutation.data };
}

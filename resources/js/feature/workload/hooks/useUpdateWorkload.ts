import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useHttp } from "@inertiajs/react";
import { WorkloadPayload } from "../types/schemas";
import { mapToApi } from "../types/mappers";
import { Workload } from "../types/types";

export function useUpdateWorkload() {
    const { put, setData } = useHttp<WorkloadPayload, Workload>();
    const queryClient = useQueryClient();

    const mutation = useMutation({
        mutationFn: ({ id, payload }: { id: string; payload: WorkloadPayload }) => {
            setData(mapToApi(payload));
            return put(`/api/v1/workloads/${id}`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["workloads"] });
        },
    });

    async function update(id: string, payload: WorkloadPayload) {
        return mutation.mutateAsync({ id, payload });
    }

    return { update, isUpdatingWorkload: mutation.isPending };
}

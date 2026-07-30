import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useHttp } from "@inertiajs/react";

export function useDeleteWorkload() {
    const { delete: destroy } = useHttp();
    const queryClient = useQueryClient();

    const mutation = useMutation({
        mutationFn: (id: string) => destroy(`/api/v1/workloads/${id}`),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["workloads"] });
        },
    });

    return { remove: mutation.mutateAsync, isDeletingWorkload: mutation.isPending };
}

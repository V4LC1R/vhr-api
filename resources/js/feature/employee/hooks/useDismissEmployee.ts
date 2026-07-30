import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useHttp } from "@inertiajs/react";
import { Employee } from "../types/types";

export function useDismissEmployee() {
    const { patch } = useHttp<Record<string, never>, Employee>();
    const queryClient = useQueryClient();

    const mutation = useMutation({
        mutationFn: (id: string) => patch(`/api/v1/employees/${id}/dismiss`),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["employees"] });
        },
    });

    return { dismiss: mutation.mutateAsync, isDismissing: mutation.isPending };
}

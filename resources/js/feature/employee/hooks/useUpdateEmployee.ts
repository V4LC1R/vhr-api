import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useHttp } from "@inertiajs/react";
import { Employee } from "../types/types";

export type UpdateEmployeePayload = {
    status: string;
    workloadId: string;
    kind: string;
};

export function useUpdateEmployee() {
    const { put, setData } = useHttp<UpdateEmployeePayload, Employee>();
    const queryClient = useQueryClient();

    const mutation = useMutation({
        mutationFn: ({ id, payload }: { id: string; payload: UpdateEmployeePayload }) => {
            setData(payload);
            return put(`/api/v1/employees/${id}`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["employees"] });
        },
    });

    async function update(id: string, payload: UpdateEmployeePayload) {
        return mutation.mutateAsync({ id, payload });
    }

    return { update, isUpdatingEmployee: mutation.isPending };
}

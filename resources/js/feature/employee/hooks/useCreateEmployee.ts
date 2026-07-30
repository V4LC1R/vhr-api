import { useMutation, useQueryClient } from "@tanstack/react-query";
import { useHttp } from "@inertiajs/react";
import { Employee } from "../types/types";
import { EmployeePayload } from "../types";

export function useCreateEmployee() {
    const { post, setData } = useHttp<EmployeePayload, Employee>();
    const queryClient = useQueryClient();

    const mutation = useMutation({
        mutationFn: (payload: EmployeePayload) => {
            setData(payload);
            return post("/api/v1/employees");
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["employees"] });
        },
    });

    return { create: mutation.mutateAsync, isCreatingEmployee: mutation.isPending };
}

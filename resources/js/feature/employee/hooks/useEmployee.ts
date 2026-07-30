import { useQuery } from "@tanstack/react-query";
import { useHttp } from "@inertiajs/react";
import { Employee } from "../types/types";

export function useEmployee(id: string) {
    const { get } = useHttp<Record<string, never>, Employee>();

    const { data: employee, isLoading: isLoadingEmployee, error } = useQuery({
        queryKey: ["employees", id],
        queryFn: () => get(`/api/v1/employees/${id}`),
        enabled: !!id,
    });

    return { employee, isLoadingEmployee, error };
}

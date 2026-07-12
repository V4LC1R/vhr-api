import { useHttp } from "@inertiajs/react";
import { Employee } from "../types/types";

export function useGetEmployee() {
    const { get, processing } = useHttp<Record<string, never>, Employee>();

    async function fetchEmployee(id: string): Promise<Employee> {
        return await get(`/api/v1/employees/${id}`);
    }

    return { fetchEmployee, isLoadingEmployee: processing };
}

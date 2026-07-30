import { useQuery } from "@tanstack/react-query";
import { useHttp } from "@inertiajs/react";
import { EmployeeParamsPaginated, EmployeeResponsePaginated } from "../types/types";

export function useListEmployee(params: EmployeeParamsPaginated) {
    const { get, setData } = useHttp<EmployeeParamsPaginated, EmployeeResponsePaginated>();

    const { data: response, isLoading: isLoadingEmployees } = useQuery({
        queryKey: ["employees", "list", params],
        queryFn: () => {
            setData(params);
            return get("/api/v1/employees");
        },
        placeholderData: (previous) => previous,
    });

    return { isLoadingEmployees, ...response };
}

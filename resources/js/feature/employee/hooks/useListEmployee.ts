import { useHttp } from "@inertiajs/react";
import { EmployeeParamsPaginated, EmployeeResponsePaginated } from "../types/types";

export function  useListEmployee(defaultParams:EmployeeParamsPaginated) {
    const { get, processing, setData, data, response } = useHttp<EmployeeParamsPaginated,EmployeeResponsePaginated>();

    async function list(params?:EmployeeParamsPaginated) {

        setData({
            ...defaultParams,
            ...params
        })

        return await get("/api/v1/employees");
    }

    async function nextPage() {
        if (processing || !response || response.current_page >= response.last_page) {
            return;
        }

        return await list({ ...data, page: response.current_page + 1 });
    }

    async function prevPage() {
        if (processing || !response || response.current_page <= 1) {
            return;
        }

        return await list({ ...data, page: response.current_page - 1 });
    }

    return { list, nextPage, prevPage, isLoadingEmployees:processing,...response };
}
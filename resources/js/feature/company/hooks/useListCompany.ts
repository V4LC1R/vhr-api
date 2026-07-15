import { useHttp } from "@inertiajs/react";
import { CompanyListFilters, CompanyResponsePaginated } from "../types/types";

type CompanyListParams = {
    page?: number;
    per_page?: number;
} & CompanyListFilters;

export function useListCompany(defaultParams: CompanyListParams = {}) {
    const { get, processing, setData, data, response } = useHttp<CompanyListParams, CompanyResponsePaginated>();

    async function list(params?: CompanyListParams) {
        setData({ ...defaultParams, ...params });
        return await get("/api/v1/companies");
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

    return { list, nextPage, prevPage, isLoadingCompanies: processing, ...response };
}

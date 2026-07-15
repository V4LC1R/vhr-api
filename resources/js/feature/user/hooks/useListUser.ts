import { useHttp } from "@inertiajs/react";
import { UserListFilters, UserResponsePaginated } from "../types/types";

type UserListParams = {
    page?: number;
    per_page?: number;
} & UserListFilters;

export function useListUser(defaultParams: UserListParams = {}) {
    const { get, processing, setData, data, response } = useHttp<UserListParams, UserResponsePaginated>();

    async function list(params?: UserListParams) {
        setData({ ...defaultParams, ...params });
        return await get("/api/v1/users");
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

    return { list, nextPage, prevPage, isLoadingUsers: processing, ...response };
}

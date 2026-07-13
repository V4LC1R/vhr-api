import { useHttp } from "@inertiajs/react";
import { PaginatedResponse, PaginateParams } from "@/types/paginate-requests";
import {
    DailyEngagement,
    DailyEngagementStatus,
    DailyEngagementType,
} from "@/types/dailyEngagement/types";

export type DailyEngagementFilters = {
    employeeId?: string;
    employeeName?: string; // busca parcial (ILIKE) pelo nome do colaborador
    status?: DailyEngagementStatus;
    type?: DailyEngagementType;
    month?: string; // YYYY-MM
    date?: string; // YYYY-MM-DD
};

type Params = PaginateParams<DailyEngagementFilters>;
type Response = PaginatedResponse<DailyEngagement>;

export function useListDailyEngagements(defaultParams: Params = {}) {
    const { get, processing, setData, data, response } = useHttp<Params, Response>();

    async function list(params?: Params) {
        setData({ ...defaultParams, ...params });
        return await get("/api/v1/daily-engagements");
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

    return { list, nextPage, prevPage, isLoadingDays: processing, ...response };
}

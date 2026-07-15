import { useHttp } from "@inertiajs/react";
import { ReportFilters, ReportResponse } from "../types/types";

/**
 * As 3 telas de relatório (geral/faltas/diaristas) batem em endpoints diferentes
 * mas com a mesma forma de chamada — um hook genérico parametrizado pela URL evita
 * 3 arquivos idênticos.
 */
export function useAttendanceReport(endpoint: string) {
    const { get, processing, setData, response } = useHttp<ReportFilters, ReportResponse>();

    async function fetch(filters: ReportFilters) {
        setData(filters);
        return await get(endpoint);
    }

    return { fetch, isLoading: processing, rows: response?.data ?? [] };
}

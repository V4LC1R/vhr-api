import { useHttp } from "@inertiajs/react";
import { Employee } from "../types/types";

export function useDismissEmployee() {
    const { patch, processing } = useHttp<Record<string, never>, Employee>();

    async function dismiss(id: string) {
        return await patch(`/api/v1/employees/${id}/dismiss`);
    }

    return { dismiss, isDismissing: processing };
}

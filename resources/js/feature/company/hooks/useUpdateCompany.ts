import { useHttp } from "@inertiajs/react";
import { CompanyPayload } from "../types/schemas";
import { mapToApi } from "../types/mappers";
import { Company } from "../types/types";

export function useUpdateCompany() {
    const { put, setData, processing } = useHttp<CompanyPayload, Company>();

    async function update(id: string, payload: CompanyPayload) {
        setData(mapToApi(payload));
        return await put(`/api/v1/companies/${id}`);
    }

    return { update, isUpdatingCompany: processing };
}

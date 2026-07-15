import { useHttp } from "@inertiajs/react";
import { CompanyPayload } from "../types/schemas";
import { mapToApi } from "../types/mappers";
import { Company } from "../types/types";

export function useCreateCompany() {
    const { post, setData, processing } = useHttp<CompanyPayload, Company>();

    async function create(payload: CompanyPayload) {
        setData(mapToApi(payload));
        return await post("/api/v1/companies");
    }

    return { create, isCreatingCompany: processing };
}

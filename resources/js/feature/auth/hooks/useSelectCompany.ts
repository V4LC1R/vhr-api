import { useHttp } from "@inertiajs/react";

type SelectCompanyForm = {
    companyId: string;
};

export function useSelectCompany() {
    const { post, setData, processing } = useHttp<SelectCompanyForm>();

    async function selectCompany(companyId: string, options?: Parameters<typeof post>[1]) {
        setData({ companyId });
        return await post("/api/auth/select-company", options);
    }

    return { selectCompany, processing };
}

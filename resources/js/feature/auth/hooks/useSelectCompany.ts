import { useHttp } from "@inertiajs/react";

type SelectCompanyForm = {
    companyId: string;
};

/**
 * Troca a empresa ativa da sessão (`POST /api/auth/select-company`).
 *
 * O backend grava `companyId` na sessão; quem reflete a troca é o
 * `SetActiveCompany` na próxima requisição — por isso o chamador deve
 * dar um `router.reload()` após o sucesso para refetchar os shared props.
 */
export function useSelectCompany() {
    const { post, setData, processing } = useHttp<SelectCompanyForm>();

    async function selectCompany(companyId: string, options?: Parameters<typeof post>[1]) {
        setData({ companyId });
        return await post("/api/auth/select-company", options);
    }

    return { selectCompany, processing };
}

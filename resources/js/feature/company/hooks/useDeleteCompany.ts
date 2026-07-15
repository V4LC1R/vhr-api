import { useHttp } from "@inertiajs/react";

export function useDeleteCompany() {
    const { delete: destroy, processing } = useHttp();

    async function remove(id: string) {
        return await destroy(`/api/v1/companies/${id}`);
    }

    return { remove, isDeletingCompany: processing };
}

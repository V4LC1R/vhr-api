import { useHttp } from "@inertiajs/react";

export function useDeleteWorkload() {
    const { delete: destroy, processing } = useHttp();

    async function remove(id: string) {
        return await destroy(`/api/v1/workloads/${id}`);
    }

    return { remove, isDeletingWorkload: processing };
}

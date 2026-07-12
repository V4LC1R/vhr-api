import { useHttp } from "@inertiajs/react";

export function useDeleteTimeEntry() {
    const { delete: destroy, processing } = useHttp();

    async function remove(id: string) {
        return await destroy(`/api/v1/time-entries/${id}`);
    }

    return { remove, isDeletingTimeEntry: processing };
}

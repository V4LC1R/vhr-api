import { useHttp } from "@inertiajs/react";

export function useDeleteUser() {
    const { delete: destroy, processing } = useHttp();

    async function remove(id: string) {
        return await destroy(`/api/v1/users/${id}`);
    }

    return { remove, isDeletingUser: processing };
}

import { useHttp } from "@inertiajs/react";
import { UpdateUserPayload } from "../types/schemas";
import { mapUpdateToApi } from "../types/mappers";
import { User } from "../types/types";

export function useUpdateUser() {
    const { put, setData, processing } = useHttp<ReturnType<typeof mapUpdateToApi>, User>();

    async function update(id: string, payload: UpdateUserPayload) {
        setData(mapUpdateToApi(payload));
        return await put(`/api/v1/users/${id}`);
    }

    return { update, isUpdatingUser: processing };
}

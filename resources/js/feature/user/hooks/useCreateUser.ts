import { useHttp } from "@inertiajs/react";
import { CreateUserPayload } from "../types/schemas";
import { mapCreateToApi } from "../types/mappers";
import { User } from "../types/types";

export function useCreateUser() {
    const { post, setData, processing } = useHttp<ReturnType<typeof mapCreateToApi>, User>();

    async function create(payload: CreateUserPayload) {
        setData(mapCreateToApi(payload));
        return await post("/api/v1/users");
    }

    return { create, isCreatingUser: processing };
}

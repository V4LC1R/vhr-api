import { useHttp } from "@inertiajs/react";
import { PersonPayload } from "../types/schemas";
import { mapToApi } from "../types/mappers";
import { Person } from "../types/types";

export function useCreatePerson() {
    const { post, setData, processing } = useHttp<PersonPayload, Person>();

    async function create(payload: PersonPayload) {
        setData(mapToApi(payload));
        return await post("/api/v1/persons");
    }

    return { create, isCreatingPerson: processing };
}

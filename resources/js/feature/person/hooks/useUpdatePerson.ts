import { useHttp } from "@inertiajs/react";
import { PersonPayload } from "../types/schemas";
import { mapToApi } from "../types/mappers";
import { Person } from "../types/types";

export function useUpdatePerson() {
    const { put, setData, processing } = useHttp<PersonPayload, Person>();

    async function update(id: string, payload: PersonPayload) {
        setData(mapToApi(payload));
        return await put(`/api/v1/persons/${id}`);
    }

    return { update, isUpdatingPerson: processing };
}

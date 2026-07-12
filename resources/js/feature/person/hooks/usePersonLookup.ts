import { useHttp } from "@inertiajs/react";
import { Person } from "../types/types";

export function usePersonLookup() {
    const { get, processing } = useHttp<Record<string, never>, Person>();

    async function lookup(cpf: string): Promise<Person | null> {
        try {
            return await get(`/api/v1/persons/lookup?cpf=${encodeURIComponent(cpf)}`);
        } catch (error) {
            const status = (error as { response?: { status?: number } } | undefined)?.response?.status;
            if (status === 404) return null;
            throw error;
        }
    }

    return { lookup, isLookingUp: processing };
}

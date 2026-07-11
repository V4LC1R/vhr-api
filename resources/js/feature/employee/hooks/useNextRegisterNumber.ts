import { useHttp } from "@inertiajs/react";

type NextRegisterNumberResponse = {
    registerNumber: number;
};

export function useNextRegisterNumber() {
    const { get, processing, response } = useHttp<Record<string, never>, NextRegisterNumberResponse>();

    async function fetchNextRegisterNumber() {
        return await get("/api/v1/employees/next-register-number");
    }

    return { fetchNextRegisterNumber, isLoadingRegisterNumber: processing, registerNumber: response?.registerNumber };
}

import { useQuery } from "@tanstack/react-query";
import { useHttp } from "@inertiajs/react";

type NextRegisterNumberResponse = {
    registerNumber: number;
};

export function useNextRegisterNumber() {
    const { get } = useHttp<Record<string, never>, NextRegisterNumberResponse>();

    const { data, isLoading: isLoadingRegisterNumber } = useQuery({
        queryKey: ["employees", "next-register-number"],
        queryFn: () => get("/api/v1/employees/next-register-number"),
    });

    return { isLoadingRegisterNumber, registerNumber: data?.registerNumber };
}

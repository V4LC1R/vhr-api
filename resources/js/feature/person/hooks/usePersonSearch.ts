import { useEffect, useState } from "react";
import { useHttp } from "@inertiajs/react";

import { PaginatedResponse } from "@/types/paginate-requests";
import { Person } from "../types/types";

type Params = { name?: string; per_page?: number };

/** Busca de pessoas por nome (ILIKE no back) com debounce — p/ achar quem já passou pela empresa. */
export function usePersonSearch() {
    const [query, setQuery] = useState("");

    const { get, processing, setData, response } = useHttp<Params, PaginatedResponse<Person>>();

    useEffect(() => {
        const handle = setTimeout(() => {
            setData({ per_page: 20, ...(query.trim() ? { name: query.trim() } : {}) });
            get("/api/v1/persons");
        }, 300);

        return () => clearTimeout(handle);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [query]);

    return {
        persons: response?.data ?? [],
        isSearchingPersons: processing,
        query,
        setQuery,
    };
}

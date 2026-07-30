import { useEffect, useState } from "react"

import { EmploymentType } from "@/types/employment/types"
import { useListEmployee } from "@/feature/employee/hooks/useListEmployee"

type UseEmployeeSearchParams = {
    /** Restringe aos vínculos informados (filtro `kind` da API, aceita lista). */
    kinds?: EmploymentType[]
}

/** Busca de colaboradores server-side (filtro `name` da API) com debounce. */
export function useEmployeeSearch({ kinds }: UseEmployeeSearchParams = {}) {
    const [query, setQuery] = useState("")
    const [debouncedQuery, setDebouncedQuery] = useState("")

    useEffect(() => {
        const handle = setTimeout(() => setDebouncedQuery(query), 300)
        return () => clearTimeout(handle)
    }, [query])

    const kindFilter = kinds?.length ? kinds.join(",") : undefined

    const { data, isLoadingEmployees } = useListEmployee({
        per_page: 200,
        sort: "registerNumber",
        filter: {
            ...(kindFilter ? { kind: kindFilter } : {}),
            ...(debouncedQuery ? { name: debouncedQuery } : {}),
        },
    })

    return {
        employees: data ?? [],
        isLoadingEmployees,
        query,
        setQuery,
    }
}

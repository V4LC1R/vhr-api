import { useEffect, useState } from "react"

import { useListEmployee } from "@/feature/employee/hooks/useListEmployee"

/** Busca de colaboradores server-side (filtro `name` da API) com debounce. */
export function useEmployeeSearch() {
    const [query, setQuery] = useState("")

    const { list, data, isLoadingEmployees } = useListEmployee({
        per_page: 200,
        sort: "registerNumber",
    })

    useEffect(() => {
        const handle = setTimeout(() => {
            list(query ? { filter: { name: query } } : undefined)
        }, 300)

        return () => clearTimeout(handle)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [query])

    return {
        employees: data ?? [],
        isLoadingEmployees,
        query,
        setQuery,
    }
}

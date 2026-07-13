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

    const { list, data, isLoadingEmployees } = useListEmployee({
        per_page: 200,
        sort: "registerNumber",
    })

    const kindFilter = kinds?.length ? kinds.join(",") : undefined

    useEffect(() => {
        const handle = setTimeout(() => {
            list({
                filter: {
                    ...(kindFilter ? { kind: kindFilter } : {}),
                    ...(query ? { name: query } : {}),
                },
            })
        }, 300)

        return () => clearTimeout(handle)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [query, kindFilter])

    return {
        employees: data ?? [],
        isLoadingEmployees,
        query,
        setQuery,
    }
}

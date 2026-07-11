import { useEffect, useState } from "react"
import { useListEmployee } from "../hooks/useListEmployee"
import { EmployeeTable } from "../components/table"
import { EmployeeFilters } from "../components/filters/employee-filters"
import { EmployeeListFilters } from "../types/types"

export function EmployeeListView() {
    const [filters, setFilters] = useState<EmployeeListFilters>({})

    const {
        list,
        nextPage,
        prevPage,
        isLoadingEmployees,
        data,
        current_page,
        last_page,
        total,
    } = useListEmployee({
        per_page:15,
        page:1,
        sort:'registerNumber'
    })

    useEffect(() => {
        list({ filter: filters, page: 1 })
    }, [filters])

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <EmployeeFilters value={filters} onChange={setFilters} />
            <EmployeeTable
                data={data ?? []}
                currentPage={current_page ?? 1}
                lastPage={last_page ?? 1}
                total={total ?? 0}
                isLoading={isLoadingEmployees}
                next={nextPage}
                prev={prevPage}
            />
        </div>
    )
}

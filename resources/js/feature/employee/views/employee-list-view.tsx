import { useState } from "react"
import { useListEmployee } from "../hooks/useListEmployee"
import { EmployeeTable } from "../components/table"
import { EmployeeFilters } from "../components/filters/employee-filters"
import { EmployeeListFilters } from "../types/types"

export function EmployeeListView() {
    const [filters, setFilters] = useState<EmployeeListFilters>({})
    const [page, setPage] = useState(1)

    const {
        isLoadingEmployees,
        data,
        current_page,
        last_page,
        total,
    } = useListEmployee({
        per_page: 15,
        page,
        sort: 'registerNumber',
        filter: filters,
    })

    function handleFiltersChange(next: EmployeeListFilters) {
        setFilters(next)
        setPage(1)
    }

    function nextPage() {
        if (current_page && last_page && current_page < last_page) setPage(current_page + 1)
    }

    function prevPage() {
        if (current_page && current_page > 1) setPage(current_page - 1)
    }

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <EmployeeFilters value={filters} onChange={handleFiltersChange} />
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

import { useMemo } from "react"
import { DataTable } from "@/components/table/data-table"
import { TablePagination } from "@/components/table/pagination"
import { useIsMobile } from "@/hooks/use-mobile"
import { getEmployeeColumns } from "./columns"
import { EmployeeDetail } from "./employee-detail"
import { Employee } from "../../types/types"

type Props = {
    data: Employee[]
    currentPage: number
    lastPage: number
    total: number
    isLoading?: boolean
    next: () => void
    prev: () => void
    onDismissed?: () => void
}

export function EmployeeTable({ data, currentPage, lastPage, total, isLoading, next, prev, onDismissed }: Props) {
    const isMobile = useIsMobile()
    const columns = useMemo(() => getEmployeeColumns(isMobile, onDismissed), [isMobile, onDismissed])

    return (
        <DataTable
            data={data}
            columns={columns}
            isLoading={isLoading}
            rowClassName={(row) => row.getIsExpanded() ? "has-aria-expanded:bg-amber-50 dark:has-aria-expanded:bg-[#2A2413]" : undefined}
            renderExpandedRow={(row) => <EmployeeDetail employee={row.original} />}
            footer={
                <TablePagination
                    currentPage={currentPage}
                    lastPage={lastPage}
                    total={total}
                    isLoading={isLoading}
                    onNext={next}
                    onPrevious={prev}
                />
            }
        />
    )
}
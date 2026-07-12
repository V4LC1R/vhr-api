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
}

export function EmployeeTable({ data, currentPage, lastPage, total, isLoading, next, prev }: Props) {
    const isMobile = useIsMobile()
    const columns = useMemo(() => getEmployeeColumns(isMobile), [isMobile])

    return (
        <DataTable
            data={data}
            columns={columns}
            isLoading={isLoading}
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
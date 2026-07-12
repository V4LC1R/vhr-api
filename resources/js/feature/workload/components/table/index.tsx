import { useMemo } from "react"
import { DataTable } from "@/components/table/data-table"
import { TablePagination } from "@/components/table/pagination"
import { useIsMobile } from "@/hooks/use-mobile"
import { getWorkloadColumns } from "./columns"
import { Workload } from "../../types/types"

type Props = {
    data: Workload[]
    currentPage: number
    lastPage: number
    total: number
    isLoading?: boolean
    next: () => void
    prev: () => void
    onChanged?: () => void
}

export function WorkloadTable({ data, currentPage, lastPage, total, isLoading, next, prev, onChanged }: Props) {
    const isMobile = useIsMobile()
    const columns = useMemo(() => getWorkloadColumns(isMobile, onChanged), [isMobile, onChanged])

    return (
        <DataTable
            data={data}
            columns={columns}
            isLoading={isLoading}
            emptyMessage="Nenhuma jornada cadastrada."
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

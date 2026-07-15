import { useMemo } from "react"
import { DataTable } from "@/components/table/data-table"
import { TablePagination } from "@/components/table/pagination"
import { useIsMobile } from "@/hooks/use-mobile"
import { getCompanyColumns } from "./columns"
import { Company } from "../../types/types"

type Props = {
    data: Company[]
    currentPage: number
    lastPage: number
    total: number
    isLoading?: boolean
    next: () => void
    prev: () => void
    onChanged?: () => void
}

export function CompanyTable({ data, currentPage, lastPage, total, isLoading, next, prev, onChanged }: Props) {
    const isMobile = useIsMobile()
    const columns = useMemo(() => getCompanyColumns(isMobile, onChanged), [isMobile, onChanged])

    return (
        <DataTable
            data={data}
            columns={columns}
            isLoading={isLoading}
            emptyMessage="Nenhuma empresa cadastrada."
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

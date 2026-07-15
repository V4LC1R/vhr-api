import { useMemo } from "react"
import { DataTable } from "@/components/table/data-table"
import { TablePagination } from "@/components/table/pagination"
import { useIsMobile } from "@/hooks/use-mobile"
import { getUserColumns } from "./columns"
import { User } from "../../types/types"

type Props = {
    data: User[]
    currentPage: number
    lastPage: number
    total: number
    isLoading?: boolean
    next: () => void
    prev: () => void
    onChanged?: () => void
}

export function UserTable({ data, currentPage, lastPage, total, isLoading, next, prev, onChanged }: Props) {
    const isMobile = useIsMobile()
    const columns = useMemo(() => getUserColumns(isMobile, onChanged), [isMobile, onChanged])

    return (
        <DataTable
            data={data}
            columns={columns}
            isLoading={isLoading}
            emptyMessage="Nenhum usuário cadastrado."
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

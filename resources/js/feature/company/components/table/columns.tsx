import { ColumnDef } from "@tanstack/react-table"
import { Company } from "../../types/types"
import { CompanyRowActions } from "./company-row-actions"

function formatCnpj(cnpj: string) {
    const digits = cnpj.replace(/\D/g, "")
    if (digits.length !== 14) return cnpj

    return digits.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, "$1.$2.$3/$4-$5")
}

export function getCompanyColumns(isMobile: boolean, onChanged?: () => void): ColumnDef<Company>[] {
    const columns: ColumnDef<Company>[] = [
        {
            accessorKey: "name",
            header: "Nome",
            size: 260,
        },
    ]

    if (!isMobile) {
        columns.push({
            id: "cnpj",
            header: "CNPJ",
            size: 180,
            cell: ({ row }) => formatCnpj(row.original.cnpj),
        })
    }

    columns.push({
        id: "actions",
        size: 56,
        cell: ({ row }) => <CompanyRowActions company={row.original} onChanged={onChanged} />,
    })

    return columns
}

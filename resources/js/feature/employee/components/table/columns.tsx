import { ColumnDef } from "@tanstack/react-table"
import { EllipsisIcon } from "lucide-react"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { Employee } from "../../types/types"
import { EmploymentStatus, EmploymentType } from "@/types/employment/types"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"

export const EMPLOYMENT_TYPE_LABELS: Record<EmploymentType, string> = {
    clt: "CLT",
    dayli: "Diarista",
    temporary: "Temporário",
    freelancer: "Freelancer",
}

export const EMPLOYMENT_STATUS_LABELS: Record<EmploymentStatus, string> = {
    hired: "Contratado",
    experience: "Experiência",
    left: "Desligado",
}

export const EMPLOYMENT_STATUS_VARIANTS: Record<
    EmploymentStatus,
    "default" | "secondary" | "outline"
> = {
    hired: "default",
    experience: "secondary",
    left: "outline",
}

export function getEmployeeColumns(isMobile: boolean): ColumnDef<Employee>[] {
    const columns: ColumnDef<Employee>[] = [
        {
            accessorKey: "registerNumber",
            header: "Registro",
            size: 90,
        },
        {
            accessorKey: "person.name",
            header: "Nome",
            size: 240,
        },
    ]

    if (!isMobile) {
        columns.push({
            accessorKey: "activeEmployment.kind",
            header: "Tipo",
            size: 100,
            cell: ({ row }) => {
                const kind = row.original.activeEmployment?.kind
                if (!kind) return null
                return <Badge variant="outline">{EMPLOYMENT_TYPE_LABELS[kind]}</Badge>
            },
        })
    }

    columns.push({
        accessorKey: "activeEmployment.status",
        header: "Status",
        size: 110,
        cell: ({ row }) => {
            const status = row.original.activeEmployment?.status
            if (!status) return null
            return (
                <Badge variant={EMPLOYMENT_STATUS_VARIANTS[status]}>
                    {EMPLOYMENT_STATUS_LABELS[status]}
                </Badge>
            )
        },
    })

    if (!isMobile) {
        columns.push({
            accessorKey: "activeEmployment.registerAt",
            header: "Data Registro",
            size: 120,
            cell: ({ row }) => {
                const registerAt = row.original.activeEmployment?.registerAt
                if (!registerAt) return null
                return format(new Date(registerAt), "dd/MM/yyyy", { locale: ptBR })
            },
        })
    }

    columns.push({
        id: "actions",
        size: 56,
        cell: () => (
            <DropdownMenu>
                <DropdownMenuTrigger render={<Button variant="ghost" size="icon-sm" />}>
                    <EllipsisIcon />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem>Ver detalhes</DropdownMenuItem>
                    <DropdownMenuItem>Editar</DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        ),
    })

    return columns
}

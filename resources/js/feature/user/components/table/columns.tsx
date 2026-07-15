import { ColumnDef } from "@tanstack/react-table"
import { Badge } from "@/components/ui/badge"
import { User } from "../../types/types"
import { USER_ROLE_LABELS, UserRole } from "../../types/schemas"
import { UserRowActions } from "./user-row-actions"

function roleLabel(role?: string) {
    if (!role) return "—"
    return USER_ROLE_LABELS[role as UserRole] ?? role
}

export function getUserColumns(isMobile: boolean, onChanged?: () => void): ColumnDef<User>[] {
    const columns: ColumnDef<User>[] = [
        {
            accessorKey: "email",
            header: "E-mail",
            size: 240,
        },
    ]

    if (!isMobile) {
        columns.push({
            id: "person",
            header: "Pessoa vinculada",
            size: 200,
            cell: ({ row }) => row.original.companies?.[0]?.person?.name ?? "—",
        })

        columns.push({
            id: "role",
            header: "Papel",
            size: 140,
            cell: ({ row }) => roleLabel(row.original.companies?.[0]?.role),
        })
    }

    columns.push({
        id: "status",
        header: "Status",
        size: 110,
        cell: ({ row }) => (
            <Badge variant={row.original.status === "active" ? "default" : "secondary"}>
                {row.original.status === "active" ? "Ativo" : "Inativo"}
            </Badge>
        ),
    })

    columns.push({
        id: "actions",
        size: 56,
        cell: ({ row }) => <UserRowActions user={row.original} onChanged={onChanged} />,
    })

    return columns
}

import { ColumnDef } from "@tanstack/react-table"
import { TriangleAlertIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { ApprovalGroup } from "../../types/approvals"
import { formatBalance, formatMinutes } from "../../lib/time"

export function getApprovalColumns(
    isMobile: boolean,
    daysHeader = "Pendentes"
): ColumnDef<ApprovalGroup>[] {
    const columns: ColumnDef<ApprovalGroup>[] = [
        {
            accessorKey: "name",
            header: "Colaborador",
            size: 240,
            cell: ({ row }) => (
                <div className="flex items-center gap-1.5">
                    <span className="font-medium">{row.original.name}</span>
                    {row.original.hasAnomaly && (
                        <TriangleAlertIcon
                            className="size-4 shrink-0 text-destructive"
                            aria-label="Há dias com sequência de marcações inconsistente"
                        />
                    )}
                </div>
            ),
        },
        {
            id: "days",
            header: daysHeader,
            size: 100,
            cell: ({ row }) =>
                `${row.original.days.length} ${row.original.days.length === 1 ? "dia" : "dias"}`,
        },
    ]

    if (!isMobile) {
        columns.push({
            id: "worked",
            header: () => <div className="text-right">Trabalhado</div>,
            size: 100,
            cell: ({ row }) => (
                <div className="text-right tabular-nums">
                    {formatMinutes(row.original.workedMinutes)}
                </div>
            ),
        })

        columns.push({
            id: "expected",
            header: () => <div className="text-right">Esperado</div>,
            size: 100,
            cell: ({ row }) => (
                <div className="text-right tabular-nums">
                    {formatMinutes(row.original.expectedMinutes)}
                </div>
            ),
        })
    }

    columns.push({
        id: "balance",
        header: () => <div className="text-right">Saldo</div>,
        size: 90,
        cell: ({ row }) => (
            <div
                className={cn(
                    "text-right tabular-nums",
                    row.original.balanceMinutes < 0 && "text-destructive",
                    row.original.balanceMinutes > 0 && "text-primary"
                )}
            >
                {formatBalance(row.original.balanceMinutes)}
            </div>
        ),
    })

    return columns
}

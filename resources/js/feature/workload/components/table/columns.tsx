import { ColumnDef } from "@tanstack/react-table"
import { Workload } from "../../types/types"
import { WorkloadRowActions } from "./workload-row-actions"

function formatTime(time: string) {
    return time.slice(0, 5)
}

export function getWorkloadColumns(isMobile: boolean, onChanged?: () => void): ColumnDef<Workload>[] {
    const columns: ColumnDef<Workload>[] = [
        {
            accessorKey: "description",
            header: "Descrição",
            size: 240,
        },
        {
            id: "hours",
            header: "Carga horária",
            size: 170,
            cell: ({ row }) =>
                `${row.original.weeklyHours}h/semana · ${row.original.monthlyHours}h/mês`,
        },
    ]

    if (!isMobile) {
        columns.push({
            id: "schedule",
            header: "Horário",
            size: 130,
            cell: ({ row }) =>
                `${formatTime(row.original.entryTime)} às ${formatTime(row.original.leftTime)}`,
        })

        columns.push({
            id: "interval",
            header: "Intervalo",
            size: 130,
            cell: ({ row }) => {
                const { startAt, endAt } = row.original.interval
                return startAt && endAt ? `${formatTime(startAt)} às ${formatTime(endAt)}` : "—"
            },
        })
    }

    columns.push({
        id: "actions",
        size: 56,
        cell: ({ row }) => <WorkloadRowActions workload={row.original} onChanged={onChanged} />,
    })

    return columns
}

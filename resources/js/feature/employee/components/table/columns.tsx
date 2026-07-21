import { ColumnDef } from "@tanstack/react-table"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { Employee } from "../../types/types"
import { EmploymentStatus, EmploymentType } from "@/types/employment/types"
import { Badge } from "@/components/ui/badge"
import { EmployeeRowActions } from "./employee-row-actions"
import { KindChip } from "./kind-chip"
import { StatusChip } from "./status-chipt"

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

export function getEmployeeColumns(isMobile: boolean, onDismissed?: () => void): ColumnDef<Employee>[] {
    const columns: ColumnDef<Employee>[] = [
        {
            accessorKey: "person.name",
            header: "Colaborador",
            size: 240,
            cell({row}) {
                const name = row.original.person?.name ?? ''
                const splitName = name.split(" ");
    
                const hasName = !!splitName?.length

                const firtLetter = hasName && splitName[0] ? splitName[0][0].toUpperCase(): 'S'
                const secondLetter = hasName && splitName[1] ? splitName[1][0].toUpperCase() : ''

                const number = Number(row.original.registerNumber)  > 9
                    ? `#0${row.original.registerNumber}`
                    : `#00${row.original.registerNumber}`

                return (
                    <div className="flex flex-row gap-3">
                        <div className="text-[12px] font-light bg-primary dark:font-bold dark:bg-brand-gold text-secondary p-2 h-8.75 w-8.75 text-center rounded-md">
                            <span>{`${firtLetter}${secondLetter}`}</span>
                        </div>
                        <div className="flex flex-col">
                            <span className="font-bold">{name}</span>
                            <span className="font-light text-xs text-[#9A9587]">{number}</span>
                        </div>
                    </div>
                )
            },
        },
    ]

    if (!isMobile) {
        columns.push({
            accessorKey: "activeEmployment.kind",
            header: "Vinculo",
            size: 50,
            cell: ({ row }) => {
                const kind = row.original.activeEmployment?.kind
                if (!kind) return null
                return <KindChip kind={kind}/>
            },
        })
    }

    if (!isMobile) {
        columns.push({
            accessorKey: "activeEmployment.kind.workload",
            header: "Jornada",
            size: 50,
            cell: ({ row }) => {
                
                const workload = row.original.activeEmployment?.workload
                if (!workload) return null
                return (
                    <span>
                        {workload.description}
                    </span>
                )
            },
        })
           
        columns.push({
            accessorKey: "activeEmployment.status",
            header: "Status",
            size: 20,
            cell: ({ row }) => {
                
                const workload = row.original.activeEmployment?.status
                if (!workload) return null
                return <StatusChip status={workload}/>
            },
        })
    
    }

    columns.push({
        id: "actions",
        size: 26,
        cell: ({ row }) => <EmployeeRowActions employee={row.original} onDismissed={onDismissed} />,
    })

    return columns
}

import { useEffect } from "react"
import { Loader2Icon } from "lucide-react"

import { Employee } from "@/types/employee/types"
import { useListEmployee } from "@/feature/employee/hooks/useListEmployee"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"

interface EmployeeSelectProps {
    value: string | null
    /** Entrega o colaborador completo (com activeEmployment.workload). */
    onChange: (employee: Employee) => void
}

export function EmployeeSelect({ value, onChange }: EmployeeSelectProps) {
    const { list, data: employees, isLoadingEmployees } = useListEmployee({
        per_page: 200,
        sort: "registerNumber",
    })

    useEffect(() => {
        list()
    }, [])

    const items = Object.fromEntries(
        (employees ?? []).map((employee) => [
            employee.id,
            employee.person?.name ?? `Nº ${employee.registerNumber}`,
        ])
    )

    return (
        <Select
            items={items}
            value={value}
            onValueChange={(id) => {
                const employee = employees?.find((e) => e.id === id)
                if (employee) onChange(employee)
            }}
        >
            <SelectTrigger className="w-full sm:w-72" disabled={isLoadingEmployees}>
                <SelectValue
                    placeholder={
                        isLoadingEmployees ? "Carregando colaboradores..." : "Selecione um colaborador"
                    }
                />
                {isLoadingEmployees && (
                    <Loader2Icon className="size-4 shrink-0 animate-spin text-muted-foreground" />
                )}
            </SelectTrigger>
            <SelectContent>
                {(employees ?? []).map((employee) => (
                    <SelectItem key={employee.id} value={employee.id}>
                        {employee.person?.name ?? `Nº ${employee.registerNumber}`}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    )
}

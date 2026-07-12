import * as React from "react"
import { Link } from "@inertiajs/react"
import { EllipsisIcon, EyeIcon, SquarePenIcon, UserXIcon } from "lucide-react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { ConfirmDialog } from "@/components/confirm-dialog"
import { Button } from "@/components/ui/button"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { useDismissEmployee } from "../../hooks/useDismissEmployee"
import { Employee } from "../../types/types"

interface EmployeeRowActionsProps {
    employee: Employee
    onDismissed?: () => void
}

export function EmployeeRowActions({ employee, onDismissed }: EmployeeRowActionsProps) {
    const [dismissDialogOpen, setDismissDialogOpen] = React.useState(false)
    const { dismiss, isDismissing } = useDismissEmployee()

    // `activeEmployment` só existe (relação HasOne filtrada por status hired/experience) enquanto o
    // vínculo está ativo — depois de desligado, esse campo vem null, não com status "left".
    const isDismissed = !employee.activeEmployment

    async function confirmDismiss() {
        try {
            await dismiss(employee.id)
            toast.success("Colaborador desligado com sucesso!")
            setDismissDialogOpen(false)
            onDismissed?.()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível desligar o colaborador."))
        }
    }

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger render={<Button variant="ghost" size="icon-sm" />}>
                    <EllipsisIcon />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem>
                        <EyeIcon />
                        Ver detalhes
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        render={<Link href={`/dashboard/employees/${employee.id}/edit`} />}
                    >
                        <SquarePenIcon />
                        Editar
                    </DropdownMenuItem>
                    {!isDismissed && (
                        <DropdownMenuItem
                            variant="destructive"
                            onClick={() => setDismissDialogOpen(true)}
                        >
                            <UserXIcon />
                            Demitir
                        </DropdownMenuItem>
                    )}
                </DropdownMenuContent>
            </DropdownMenu>

            <ConfirmDialog
                open={dismissDialogOpen}
                onOpenChange={setDismissDialogOpen}
                title="Desligar colaborador"
                description={`Tem certeza que deseja desligar ${employee.person?.name ?? "este colaborador"}? Essa ação encerra o vínculo atual.`}
                confirmLabel="Desligar"
                confirmIcon={UserXIcon}
                destructive
                isLoading={isDismissing}
                onConfirm={confirmDismiss}
            />
        </>
    )
}

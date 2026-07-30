import * as React from "react"
import { Link } from "@inertiajs/react"
import { UserXIcon } from "lucide-react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { ConfirmDialog } from "@/components/confirm-dialog"
import { Button } from "@/components/ui/button"

import { useDismissEmployee } from "../../hooks/useDismissEmployee"
import { Employee } from "../../types/types"
import { Icon } from "@iconify/react"

interface EmployeeRowActionsProps {
    employee: Employee
}

export function EmployeeRowActions({ employee }: EmployeeRowActionsProps) {
    const [dismissDialogOpen, setDismissDialogOpen] = React.useState(false)
    const { dismiss, isDismissing } = useDismissEmployee()

    const isDismissed = !employee.activeEmployment || !!employee.activeEmployment.leftAt

    async function confirmDismiss() {
        try {
            await dismiss(employee.id)
            toast.success("Colaborador desligado com sucesso!")
            setDismissDialogOpen(false)
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível desligar o colaborador."))
        }
    }


    return (
        <>
            <div className="flex flex-row gap-1">
                <Button
                    variant="outline"
                    size="sm"
                    nativeButton={false}
                    render={<Link href={`/dashboard/employees/${employee.id}/edit`} />}
                >
                    <Icon icon="solar:pen-linear"/>
                </Button>

                {
                    !isDismissed && (
                        <Button variant="outline" size="sm" className="border-negative/20 border-spacing-1.5 text-negative/80 font-semibold hover:text-negative">
                            Demitir
                        </Button>
                    )
                }
                
            </div>

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

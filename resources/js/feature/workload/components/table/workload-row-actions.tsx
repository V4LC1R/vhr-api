import * as React from "react"
import { EllipsisIcon, SquarePenIcon, Trash2Icon } from "lucide-react"
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
import { WorkloadFormDialog } from "../workload-form-dialog"
import { useUpdateWorkload } from "../../hooks/useUpdateWorkload"
import { useDeleteWorkload } from "../../hooks/useDeleteWorkload"
import { mapToForm } from "../../types/mappers"
import { WorkloadPayload } from "../../types/schemas"
import { Workload } from "../../types/types"

interface WorkloadRowActionsProps {
    workload: Workload
    onChanged?: () => void
}

export function WorkloadRowActions({ workload, onChanged }: WorkloadRowActionsProps) {
    const [editOpen, setEditOpen] = React.useState(false)
    const [deleteOpen, setDeleteOpen] = React.useState(false)
    const { update, isUpdatingWorkload } = useUpdateWorkload()
    const { remove, isDeletingWorkload } = useDeleteWorkload()

    async function handleUpdate(values: WorkloadPayload) {
        try {
            await update(workload.id, values)
            toast.success("Jornada atualizada com sucesso!")
            setEditOpen(false)
            onChanged?.()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível atualizar a jornada."))
        }
    }

    async function confirmDelete() {
        try {
            await remove(workload.id)
            toast.success("Jornada excluída com sucesso!")
            setDeleteOpen(false)
            onChanged?.()
        } catch (error) {
            // Ex.: 409 do back quando a jornada está vinculada a colaboradores.
            toast.error(extractErrorMessage(error, "Não foi possível excluir a jornada."))
            setDeleteOpen(false)
        }
    }

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger render={<Button variant="ghost" size="icon-sm" />}>
                    <EllipsisIcon />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem onClick={() => setEditOpen(true)}>
                        <SquarePenIcon />
                        Editar
                    </DropdownMenuItem>
                    <DropdownMenuItem variant="destructive" onClick={() => setDeleteOpen(true)}>
                        <Trash2Icon />
                        Excluir
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <WorkloadFormDialog
                open={editOpen}
                onOpenChange={setEditOpen}
                title="Editar jornada"
                description="Altere os dados da jornada de trabalho."
                submitLabel="Salvar alterações"
                submittingLabel="Salvando..."
                defaultValues={mapToForm(workload)}
                isSubmitting={isUpdatingWorkload}
                onSubmit={handleUpdate}
            />

            <ConfirmDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Excluir jornada"
                description={`Tem certeza que deseja excluir a jornada "${workload.description}"? Essa ação não pode ser desfeita.`}
                confirmLabel="Excluir"
                confirmIcon={Trash2Icon}
                destructive
                isLoading={isDeletingWorkload}
                onConfirm={confirmDelete}
            />
        </>
    )
}

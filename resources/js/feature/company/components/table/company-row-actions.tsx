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
import { CompanyFormDialog } from "../company-form-dialog"
import { useUpdateCompany } from "../../hooks/useUpdateCompany"
import { useDeleteCompany } from "../../hooks/useDeleteCompany"
import { mapToForm } from "../../types/mappers"
import { CompanyPayload } from "../../types/schemas"
import { Company } from "../../types/types"

interface CompanyRowActionsProps {
    company: Company
    onChanged?: () => void
}

export function CompanyRowActions({ company, onChanged }: CompanyRowActionsProps) {
    const [editOpen, setEditOpen] = React.useState(false)
    const [deleteOpen, setDeleteOpen] = React.useState(false)
    const { update, isUpdatingCompany } = useUpdateCompany()
    const { remove, isDeletingCompany } = useDeleteCompany()

    async function handleUpdate(values: CompanyPayload) {
        try {
            await update(company.id, values)
            toast.success("Empresa atualizada com sucesso!")
            setEditOpen(false)
            onChanged?.()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível atualizar a empresa."))
        }
    }

    async function confirmDelete() {
        try {
            await remove(company.id)
            toast.success("Empresa excluída com sucesso!")
            setDeleteOpen(false)
            onChanged?.()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível excluir a empresa."))
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

            <CompanyFormDialog
                open={editOpen}
                onOpenChange={setEditOpen}
                title="Editar empresa"
                description="Altere os dados da empresa."
                submitLabel="Salvar alterações"
                submittingLabel="Salvando..."
                defaultValues={mapToForm(company)}
                isSubmitting={isUpdatingCompany}
                onSubmit={handleUpdate}
            />

            <ConfirmDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Excluir empresa"
                description={`Tem certeza que deseja excluir a empresa "${company.name}"? Essa ação não pode ser desfeita.`}
                confirmLabel="Excluir"
                confirmIcon={Trash2Icon}
                destructive
                isLoading={isDeletingCompany}
                onConfirm={confirmDelete}
            />
        </>
    )
}

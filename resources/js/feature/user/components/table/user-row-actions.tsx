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
import { UserEditDialog } from "../user-edit-dialog"
import { useUpdateUser } from "../../hooks/useUpdateUser"
import { useDeleteUser } from "../../hooks/useDeleteUser"
import { mapToForm } from "../../types/mappers"
import { UpdateUserPayload } from "../../types/schemas"
import { User } from "../../types/types"

interface UserRowActionsProps {
    user: User
    onChanged?: () => void
}

export function UserRowActions({ user, onChanged }: UserRowActionsProps) {
    const [editOpen, setEditOpen] = React.useState(false)
    const [deleteOpen, setDeleteOpen] = React.useState(false)
    const { update, isUpdatingUser } = useUpdateUser()
    const { remove, isDeletingUser } = useDeleteUser()

    async function handleUpdate(values: UpdateUserPayload) {
        try {
            await update(user.id, values)
            toast.success("Usuário atualizado com sucesso!")
            setEditOpen(false)
            onChanged?.()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível atualizar o usuário."))
        }
    }

    async function confirmDelete() {
        try {
            await remove(user.id)
            toast.success("Usuário excluído com sucesso!")
            setDeleteOpen(false)
            onChanged?.()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível excluir o usuário."))
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

            <UserEditDialog
                open={editOpen}
                onOpenChange={setEditOpen}
                defaultValues={mapToForm(user)}
                isSubmitting={isUpdatingUser}
                onSubmit={handleUpdate}
            />

            <ConfirmDialog
                open={deleteOpen}
                onOpenChange={setDeleteOpen}
                title="Excluir usuário"
                description={`Tem certeza que deseja excluir o usuário "${user.email}"? Essa ação não pode ser desfeita.`}
                confirmLabel="Excluir"
                confirmIcon={Trash2Icon}
                destructive
                isLoading={isDeletingUser}
                onConfirm={confirmDelete}
            />
        </>
    )
}

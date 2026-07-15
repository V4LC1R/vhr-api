import { useEffect } from "react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { useListUser } from "../hooks/useListUser"
import { useCreateUser } from "../hooks/useCreateUser"
import { UserTable } from "../components/table"
import { UserCreateDialog } from "../components/user-create-dialog"
import { CreateUserPayload } from "../types/schemas"

interface UserListViewProps {
    createOpen: boolean
    onCreateOpenChange: (open: boolean) => void
}

export function UserListView({ createOpen, onCreateOpenChange }: UserListViewProps) {
    const {
        list,
        nextPage,
        prevPage,
        isLoadingUsers,
        data,
        current_page,
        last_page,
        total,
    } = useListUser({
        per_page: 15,
        page: 1,
    })

    const { create, isCreatingUser } = useCreateUser()

    useEffect(() => {
        list()
    }, [])

    function handleChanged() {
        list({ page: current_page ?? 1 })
    }

    async function handleCreate(values: CreateUserPayload) {
        try {
            await create(values)
            toast.success("Usuário cadastrado com sucesso!")
            onCreateOpenChange(false)
            await list({ page: 1 })
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível cadastrar o usuário."))
        }
    }

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <UserTable
                data={data ?? []}
                currentPage={current_page ?? 1}
                lastPage={last_page ?? 1}
                total={total ?? 0}
                isLoading={isLoadingUsers}
                next={nextPage}
                prev={prevPage}
                onChanged={handleChanged}
            />

            <UserCreateDialog
                open={createOpen}
                onOpenChange={onCreateOpenChange}
                isSubmitting={isCreatingUser}
                onSubmit={handleCreate}
            />
        </div>
    )
}

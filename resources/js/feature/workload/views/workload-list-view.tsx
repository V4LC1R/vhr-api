import { useState } from "react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { useListWorkload } from "../hooks/useListWorkload"
import { useCreateWorkload } from "../hooks/useCreateWorkload"
import { WorkloadTable } from "../components/table"
import { WorkloadFormDialog } from "../components/workload-form-dialog"
import { WorkloadPayload } from "../types/schemas"

interface WorkloadListViewProps {
    createOpen: boolean
    onCreateOpenChange: (open: boolean) => void
}

export function WorkloadListView({ createOpen, onCreateOpenChange }: WorkloadListViewProps) {
    const [page, setPage] = useState(1)

    const {
        isLoadingWorkloads,
        data,
        current_page,
        last_page,
        total,
    } = useListWorkload({
        per_page: 15,
        page,
    })

    const { create, isCreatingWorkload } = useCreateWorkload()

    function nextPage() {
        if (current_page && last_page && current_page < last_page) setPage(current_page + 1)
    }

    function prevPage() {
        if (current_page && current_page > 1) setPage(current_page - 1)
    }

    async function handleCreate(values: WorkloadPayload) {
        try {
            await create(values)
            toast.success("Jornada cadastrada com sucesso!")
            onCreateOpenChange(false)
            setPage(1)
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível cadastrar a jornada."))
        }
    }

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <WorkloadTable
                data={data ?? []}
                currentPage={current_page ?? 1}
                lastPage={last_page ?? 1}
                total={total ?? 0}
                isLoading={isLoadingWorkloads}
                next={nextPage}
                prev={prevPage}
            />

            <WorkloadFormDialog
                open={createOpen}
                onOpenChange={onCreateOpenChange}
                title="Cadastrar jornada"
                description="Crie uma nova jornada de trabalho pra empresa."
                submitLabel="Cadastrar jornada"
                submittingLabel="Cadastrando..."
                isSubmitting={isCreatingWorkload}
                onSubmit={handleCreate}
            />
        </div>
    )
}

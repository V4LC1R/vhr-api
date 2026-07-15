import { useEffect } from "react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { useListCompany } from "../hooks/useListCompany"
import { useCreateCompany } from "../hooks/useCreateCompany"
import { CompanyTable } from "../components/table"
import { CompanyFormDialog } from "../components/company-form-dialog"
import { CompanyPayload } from "../types/schemas"

interface CompanyListViewProps {
    createOpen: boolean
    onCreateOpenChange: (open: boolean) => void
}

export function CompanyListView({ createOpen, onCreateOpenChange }: CompanyListViewProps) {
    const {
        list,
        nextPage,
        prevPage,
        isLoadingCompanies,
        data,
        current_page,
        last_page,
        total,
    } = useListCompany({
        per_page: 15,
        page: 1,
    })

    const { create, isCreatingCompany } = useCreateCompany()

    useEffect(() => {
        list()
    }, [])

    function handleChanged() {
        list({ page: current_page ?? 1 })
    }

    async function handleCreate(values: CompanyPayload) {
        try {
            await create(values)
            toast.success("Empresa cadastrada com sucesso!")
            onCreateOpenChange(false)
            await list({ page: 1 })
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível cadastrar a empresa."))
        }
    }

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <CompanyTable
                data={data ?? []}
                currentPage={current_page ?? 1}
                lastPage={last_page ?? 1}
                total={total ?? 0}
                isLoading={isLoadingCompanies}
                next={nextPage}
                prev={prevPage}
                onChanged={handleChanged}
            />

            <CompanyFormDialog
                open={createOpen}
                onOpenChange={onCreateOpenChange}
                title="Cadastrar empresa"
                description="Você se torna owner da empresa automaticamente ao criá-la."
                submitLabel="Cadastrar empresa"
                submittingLabel="Cadastrando..."
                isSubmitting={isCreatingCompany}
                onSubmit={handleCreate}
            />
        </div>
    )
}

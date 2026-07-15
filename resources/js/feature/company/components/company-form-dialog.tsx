import * as React from "react"
import { useForm } from "react-hook-form"
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema"

import { companySchema, CompanyPayload } from "../types/schemas"
import { Button } from "@/components/ui/button"
import { RHF } from "@/components/rhf-fields"
import { FieldGroup } from "@/components/ui/field"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"

export const emptyCompanyForm: CompanyPayload = {
    name: "",
    cnpj: "",
}

interface CompanyFormDialogProps {
    open: boolean
    onOpenChange: (open: boolean) => void
    title: string
    description?: string
    submitLabel: string
    submittingLabel: string
    defaultValues?: CompanyPayload
    isSubmitting?: boolean
    onSubmit: (values: CompanyPayload) => Promise<void> | void
}

export function CompanyFormDialog({
    open,
    onOpenChange,
    title,
    description,
    submitLabel,
    submittingLabel,
    defaultValues,
    isSubmitting,
    onSubmit,
}: CompanyFormDialogProps) {
    const form = useForm<CompanyPayload>({
        resolver: standardSchemaResolver(companySchema),
        defaultValues: defaultValues ?? emptyCompanyForm,
    })

    // Reseta só na abertura — resetar em qualquer re-render do pai perderia o
    // que o usuário já digitou.
    React.useEffect(() => {
        if (open) form.reset(defaultValues ?? emptyCompanyForm)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open])

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description && <DialogDescription>{description}</DialogDescription>}
                </DialogHeader>

                <FieldGroup>
                    <RHF.Input
                        name="name"
                        label="Nome"
                        control={form.control}
                        placeholder="Empresa LTDA"
                    />
                    <RHF.Input
                        name="cnpj"
                        label="CNPJ"
                        control={form.control}
                        placeholder="00.000.000/0000-00"
                    />
                </FieldGroup>

                <DialogFooter>
                    <DialogClose render={<Button variant="outline" />}>Cancelar</DialogClose>
                    <Button disabled={isSubmitting} onClick={form.handleSubmit(onSubmit)}>
                        {isSubmitting ? submittingLabel : submitLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

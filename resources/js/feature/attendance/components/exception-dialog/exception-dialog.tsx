import * as React from "react"
import { Controller, useForm } from "react-hook-form"
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema"

import { DAILY_ENGAGEMENT_TYPES } from "@/types/dailyEngagement/types"
import { DAY_TYPE_LABELS } from "../../lib/labels"
import { exceptionSchema, ExceptionPayload } from "../../types/schemas"
import { Button } from "@/components/ui/button"
import { RHF } from "@/components/rhf-fields"
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"

const emptyException: ExceptionPayload = {
    type: "work",
    note: "",
}

interface ExceptionDialogProps {
    open: boolean
    onOpenChange: (open: boolean) => void
    description?: string
    defaultValues?: ExceptionPayload
    isSubmitting?: boolean
    onSubmit: (values: ExceptionPayload) => Promise<void> | void
}

export function ExceptionDialog({
    open,
    onOpenChange,
    description,
    defaultValues,
    isSubmitting,
    onSubmit,
}: ExceptionDialogProps) {
    const form = useForm<ExceptionPayload>({
        resolver: standardSchemaResolver(exceptionSchema),
        defaultValues: defaultValues ?? emptyException,
    })

    React.useEffect(() => {
        if (open) form.reset(defaultValues ?? emptyException)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open])

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>Tipo do dia</DialogTitle>
                    <DialogDescription>
                        {description ??
                            "Marque folga, feriado, atestado ou falta. O dia volta pra rascunho e precisa ser reenviado."}
                    </DialogDescription>
                </DialogHeader>

                <FieldGroup>
                    <Controller
                        control={form.control}
                        name="type"
                        render={({ field, fieldState }) => (
                            <Field data-invalid={fieldState.invalid}>
                                <FieldLabel htmlFor="day-type">Tipo</FieldLabel>
                                <Select
                                    items={DAY_TYPE_LABELS}
                                    value={field.value}
                                    onValueChange={field.onChange}
                                >
                                    <SelectTrigger id="day-type" className="w-full">
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {DAILY_ENGAGEMENT_TYPES.map((type) => (
                                            <SelectItem key={type} value={type}>
                                                {DAY_TYPE_LABELS[type]}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError errors={fieldState.error ? [fieldState.error] : undefined} />
                            </Field>
                        )}
                    />

                    <RHF.Input
                        name="note"
                        label="Observação (opcional)"
                        control={form.control}
                        placeholder="Ex.: atestado entregue, feriado municipal..."
                    />
                </FieldGroup>

                <DialogFooter>
                    <DialogClose render={<Button variant="outline" />}>Cancelar</DialogClose>
                    <Button disabled={isSubmitting} onClick={form.handleSubmit(onSubmit)}>
                        {isSubmitting ? "Salvando..." : "Salvar"}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

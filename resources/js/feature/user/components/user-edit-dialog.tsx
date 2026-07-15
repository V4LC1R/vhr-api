import * as React from "react"
import { Controller, useForm } from "react-hook-form"
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema"

import { updateUserSchema, UpdateUserPayload, USER_ROLES, USER_ROLE_LABELS } from "../types/schemas"
import { Button } from "@/components/ui/button"
import { RHF } from "@/components/rhf-fields"
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"

const STATUS_LABELS = { active: "Ativo", inactive: "Inativo" } as const

interface UserEditDialogProps {
    open: boolean
    onOpenChange: (open: boolean) => void
    defaultValues: UpdateUserPayload
    isSubmitting?: boolean
    onSubmit: (values: UpdateUserPayload) => Promise<void> | void
}

/** Edição administrativa — email, senha (opcional, mantém a atual se vazia), papel e status. */
export function UserEditDialog({ open, onOpenChange, defaultValues, isSubmitting, onSubmit }: UserEditDialogProps) {
    const form = useForm<UpdateUserPayload>({
        resolver: standardSchemaResolver(updateUserSchema),
        defaultValues,
    })

    React.useEffect(() => {
        if (open) form.reset(defaultValues)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open])

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Editar usuário</DialogTitle>
                    <DialogDescription>Altere e-mail, senha, papel ou status do usuário.</DialogDescription>
                </DialogHeader>

                <FieldGroup>
                    <RHF.Input name="email" label="E-mail" type="email" control={form.control} />
                    <RHF.Input
                        name="password"
                        label="Nova senha"
                        type="password"
                        control={form.control}
                        placeholder="Deixe em branco para manter a atual"
                    />

                    <Controller
                        control={form.control}
                        name="role"
                        render={({ field, fieldState }) => (
                            <Field data-invalid={fieldState.invalid}>
                                <FieldLabel htmlFor="role">Papel</FieldLabel>
                                <Select items={USER_ROLE_LABELS} value={field.value} onValueChange={field.onChange}>
                                    <SelectTrigger id="role" className="w-full">
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {USER_ROLES.map((role) => (
                                            <SelectItem key={role} value={role}>
                                                {USER_ROLE_LABELS[role]}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <FieldError errors={fieldState.error ? [fieldState.error] : undefined} />
                            </Field>
                        )}
                    />

                    <Controller
                        control={form.control}
                        name="status"
                        render={({ field, fieldState }) => (
                            <Field data-invalid={fieldState.invalid}>
                                <FieldLabel htmlFor="status">Status</FieldLabel>
                                <Select items={STATUS_LABELS} value={field.value} onValueChange={field.onChange}>
                                    <SelectTrigger id="status" className="w-full">
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Ativo</SelectItem>
                                        <SelectItem value="inactive">Inativo</SelectItem>
                                    </SelectContent>
                                </Select>
                                <FieldError errors={fieldState.error ? [fieldState.error] : undefined} />
                            </Field>
                        )}
                    />
                </FieldGroup>

                <DialogFooter>
                    <DialogClose render={<Button variant="outline" />}>Cancelar</DialogClose>
                    <Button disabled={isSubmitting} onClick={form.handleSubmit(onSubmit)}>
                        {isSubmitting ? "Salvando..." : "Salvar alterações"}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

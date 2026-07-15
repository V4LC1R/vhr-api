import * as React from "react"
import { Controller, useForm } from "react-hook-form"
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema"
import { XIcon } from "lucide-react"

import { createUserSchema, CreateUserPayload, USER_ROLES, USER_ROLE_LABELS } from "../types/schemas"
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
import { PersonSearchCombobox } from "@/feature/person/components/person-search-combobox"
import { Person } from "@/feature/person/types/types"

const emptyUserForm: CreateUserPayload = {
    email: "",
    password: "",
    role: "employee",
    personId: null,
}

interface UserCreateDialogProps {
    open: boolean
    onOpenChange: (open: boolean) => void
    isSubmitting?: boolean
    onSubmit: (values: CreateUserPayload) => Promise<void> | void
}

/** Cadastro de usuário — só existe modo "criar" aqui; edição é `UserEditDialog`. */
export function UserCreateDialog({ open, onOpenChange, isSubmitting, onSubmit }: UserCreateDialogProps) {
    const [selectedPerson, setSelectedPerson] = React.useState<Person | null>(null)

    const form = useForm<CreateUserPayload>({
        resolver: standardSchemaResolver(createUserSchema),
        defaultValues: emptyUserForm,
    })

    React.useEffect(() => {
        if (open) {
            form.reset(emptyUserForm)
            setSelectedPerson(null)
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open])

    function handleSelectPerson(person: Person) {
        setSelectedPerson(person)
        form.setValue("personId", person.id)
    }

    function clearPerson() {
        setSelectedPerson(null)
        form.setValue("personId", null)
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>Cadastrar usuário</DialogTitle>
                    <DialogDescription>
                        Cria um acesso ao sistema pra empresa ativa, já com o papel selecionado.
                    </DialogDescription>
                </DialogHeader>

                <FieldGroup>
                    <RHF.Input
                        name="email"
                        label="E-mail"
                        type="email"
                        control={form.control}
                        placeholder="usuario@empresa.com"
                    />
                    <RHF.Input
                        name="password"
                        label="Senha"
                        type="password"
                        control={form.control}
                        placeholder="Mínimo 8 caracteres, com maiúscula, número e símbolo"
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

                    <Field>
                        <FieldLabel>Pessoa vinculada (opcional)</FieldLabel>
                        {selectedPerson ? (
                            <div className="flex items-center justify-between rounded-lg border border-input px-3 py-2 text-sm">
                                <span className="truncate">{selectedPerson.name}</span>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="icon-sm"
                                    onClick={clearPerson}
                                    aria-label="Remover vínculo com a pessoa"
                                >
                                    <XIcon />
                                </Button>
                            </div>
                        ) : (
                            <PersonSearchCombobox onSelect={handleSelectPerson} placeholder="Buscar pessoa por nome..." />
                        )}
                    </Field>
                </FieldGroup>

                <DialogFooter>
                    <DialogClose render={<Button variant="outline" />}>Cancelar</DialogClose>
                    <Button disabled={isSubmitting} onClick={form.handleSubmit(onSubmit)}>
                        {isSubmitting ? "Cadastrando..." : "Cadastrar usuário"}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

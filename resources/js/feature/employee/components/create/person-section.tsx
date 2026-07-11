import * as React from "react"
import { CheckCircle2Icon, Loader2Icon } from "lucide-react"

import { Person } from "@/feature/person/types/types"
import { usePersonLookup } from "@/feature/person/hooks/usePersonLookup"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Field, FieldError, FieldLabel } from "@/components/ui/field"
import { Input } from "@/components/ui/input"

interface PersonSectionProps {
    cpf: string
    onCpfChange: (cpf: string) => void
    name: string
    onNameChange: (name: string) => void
    email: string
    onEmailChange: (email: string) => void
    cellphone: string
    onCellphoneChange: (cellphone: string) => void
    matchedPerson: Person | null
    onMatch: (person: Person | null) => void
    errors?: { cpf?: string; name?: string; email?: string; cellphone?: string }
}

function formatCpf(digits: string) {
    return digits
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d{1,2})$/, "$1-$2")
}

export function PersonSection({
    cpf,
    onCpfChange,
    name,
    onNameChange,
    email,
    onEmailChange,
    cellphone,
    onCellphoneChange,
    matchedPerson,
    onMatch,
    errors,
}: PersonSectionProps) {
    const { lookup, isLookingUp } = usePersonLookup()

    React.useEffect(() => {
        const digits = cpf.replace(/\D/g, "")
        if (digits.length !== 11) {
            onMatch(null)
            return
        }

        let cancelled = false
        const timeout = setTimeout(async () => {
            const person = await lookup(digits)
            if (!cancelled) onMatch(person)
        }, 400)

        return () => {
            cancelled = true
            clearTimeout(timeout)
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [cpf])

    const fieldsReadOnly = !!matchedPerson

    return (
        <Card>
            <CardHeader>
                <CardTitle>Pessoa</CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Field className="sm:w-64">
                        <FieldLabel htmlFor="person-cpf">CPF</FieldLabel>
                        <div className="relative">
                            <Input
                                id="person-cpf"
                                placeholder="000.000.000-00"
                                value={formatCpf(cpf.replace(/\D/g, "").slice(0, 11))}
                                onChange={(e) => onCpfChange(e.target.value.replace(/\D/g, "").slice(0, 11))}
                                aria-invalid={!!errors?.cpf}
                                maxLength={14}
                            />
                            {isLookingUp && (
                                <Loader2Icon className="absolute top-1/2 right-2.5 size-4 -translate-y-1/2 animate-spin text-muted-foreground" />
                            )}
                        </div>
                        <FieldError errors={errors?.cpf ? [{ message: errors.cpf }] : undefined} />
                    </Field>

                    {matchedPerson && (
                        <div className="flex items-center gap-1.5 text-sm text-accent sm:mt-6 sm:flex-1">
                            <CheckCircle2Icon className="size-4 shrink-0" />
                            <span className="sm:whitespace-nowrap">
                                Pessoa já cadastrada — dados preenchidos automaticamente
                            </span>
                        </div>
                    )}
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <Field>
                        <FieldLabel htmlFor="person-name">Nome</FieldLabel>
                        <Input
                            id="person-name"
                            value={name}
                            readOnly={fieldsReadOnly}
                            onChange={(e) => onNameChange(e.target.value)}
                            aria-invalid={!!errors?.name}
                        />
                        <FieldError errors={errors?.name ? [{ message: errors.name }] : undefined} />
                    </Field>
                    <Field>
                        <FieldLabel htmlFor="person-email">E-mail</FieldLabel>
                        <Input
                            id="person-email"
                            type="email"
                            value={email}
                            readOnly={fieldsReadOnly}
                            onChange={(e) => onEmailChange(e.target.value)}
                            aria-invalid={!!errors?.email}
                        />
                        <FieldError errors={errors?.email ? [{ message: errors.email }] : undefined} />
                    </Field>
                    <Field>
                        <FieldLabel htmlFor="person-cellphone">Celular</FieldLabel>
                        <Input
                            id="person-cellphone"
                            value={cellphone}
                            readOnly={fieldsReadOnly}
                            onChange={(e) => onCellphoneChange(e.target.value)}
                            aria-invalid={!!errors?.cellphone}
                        />
                        <FieldError errors={errors?.cellphone ? [{ message: errors.cellphone }] : undefined} />
                    </Field>
                </div>
            </CardContent>
        </Card>
    )
}

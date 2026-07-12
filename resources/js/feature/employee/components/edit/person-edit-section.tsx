import { Field, FieldError, FieldLabel, FieldLegend } from "@/components/ui/field"
import { Input } from "@/components/ui/input"

interface PersonEditSectionProps {
    cpf: string
    name: string
    onNameChange: (name: string) => void
    email: string
    onEmailChange: (email: string) => void
    cellphone: string
    onCellphoneChange: (cellphone: string) => void
    pixKey: string
    onPixKeyChange: (pixKey: string) => void
    errors?: { name?: string; email?: string; cellphone?: string; pixKey?: string }
}

function formatCpf(digits: string) {
    return digits
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d{1,2})$/, "$1-$2")
}

export function PersonEditSection({
    cpf,
    name,
    onNameChange,
    email,
    onEmailChange,
    cellphone,
    onCellphoneChange,
    pixKey,
    onPixKeyChange,
    errors,
}: PersonEditSectionProps) {
    return (
        <div className="flex flex-col gap-4">
            <FieldLegend variant="label">Pessoa</FieldLegend>

            <Field className="sm:w-64">
                <FieldLabel htmlFor="person-cpf">CPF</FieldLabel>
                <Input id="person-cpf" value={formatCpf(cpf)} disabled />
            </Field>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Field>
                    <FieldLabel htmlFor="person-name">Nome</FieldLabel>
                    <Input
                        id="person-name"
                        value={name}
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
                        onChange={(e) => onCellphoneChange(e.target.value)}
                        aria-invalid={!!errors?.cellphone}
                    />
                    <FieldError errors={errors?.cellphone ? [{ message: errors.cellphone }] : undefined} />
                </Field>
                <Field>
                    <FieldLabel htmlFor="person-pix-key">Chave Pix</FieldLabel>
                    <Input
                        id="person-pix-key"
                        value={pixKey}
                        onChange={(e) => onPixKeyChange(e.target.value)}
                        aria-invalid={!!errors?.pixKey}
                    />
                    <FieldError errors={errors?.pixKey ? [{ message: errors.pixKey }] : undefined} />
                </Field>
            </div>
        </div>
    )
}

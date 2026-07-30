import { Loader2Icon } from "lucide-react"

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"

import { RHF } from "@/components/rhf-fields"
import { useFormContext } from "react-hook-form"
import { EmployeePayload } from "@/feature/employee/types"
import { useCpfField } from "../hooks/useCpfField";

type Props ={
    autoComplete?:boolean
}

export function PersonSection({autoComplete=true}:Props) {
    const {control} = useFormContext<EmployeePayload>()


    const {isLookingUp,hasCpf} = useCpfField('person.cpf',autoComplete)

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex flex-row gap-2">
                    Pessoa
                     {isLookingUp && (
                        <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                            <Loader2Icon className="size-4 animate-spin" />
                            Consultando CPF...
                        </div>
                    )}
                </CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
               

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <RHF.InputMask
                        control={control}
                        name="person.cpf"
                        label="CPF"
                        mask="###.###.###-##"
                    />

                     <RHF.Input
                        control={control}
                        label="Nome"
                        name="person.name"
                        disabled={!hasCpf}
                        containerClassName="col-span-2"
                    />

                    <RHF.DatePicker
                        control={control}
                        label="Data de Nascimento"
                        name="person.birthdate"
                        disabled={!hasCpf}
                        limits={[{
                            after:new Date()
                        }]}
                    />
 
                    <RHF.Input
                        control={control}
                        label="E-mail"
                        name="person.email"
                        type="email"
                        disabled={!hasCpf}
                    />

                    <RHF.InputMask
                        control={control}
                        label="Telefone celular"
                        name="person.cellphone"
                        mask="(##) # ####-####"
                        disabled={!hasCpf}
                    />

                    <RHF.Input
                        control={control}
                        label="Chave PIX"
                        name="person.pixKey"
                        disabled={!hasCpf}
                        containerClassName="col-span-2"
                    />
                    
                </div>
            </CardContent>
        </Card>
    )
}

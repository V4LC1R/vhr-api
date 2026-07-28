import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"

import { RHF } from "@/components/rhf-fields"
import { useFormContext, useWatch } from "react-hook-form"
import { EmployeePayload } from "@/feature/employee/types"


export function PersonSection() {
    const {control} = useFormContext<EmployeePayload>()

    const cpf:string = useWatch({name:'cpf'})

    const hasCpf = cpf != undefined && !!cpf && cpf.length >=  14

    return (
        <Card>
            <CardHeader>
                <CardTitle>Pessoa</CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-4">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <RHF.InputMask
                        control={control}
                        name="cpf"
                        label="CPF"
                        mask="###.###.###-##"
                    />

                     <RHF.Input
                        control={control}
                        label="Nome"
                        name="name"
                        disabled={!hasCpf}
                        containerClassName="col-span-2"
                    />

                    <RHF.DatePicker
                        control={control}
                        label="Data de Nascimento"
                        name="birthdate"
                        disabled={!hasCpf}
                        limits={[{
                            after:new Date()
                        }]}
                    />
 
                    <RHF.Input
                        control={control}
                        label="E-mail"
                        name="email"
                        type="email"
                        disabled={!hasCpf}
                    />

                    <RHF.InputMask
                        control={control}
                        label="Telefone celular"
                        name="cellphone"
                        mask="(##) # ####-####"
                        disabled={!hasCpf}
                    />

                    <RHF.Input
                        control={control}
                        label="Chave PIX"
                        name="pixKey"
                        disabled={!hasCpf}
                        containerClassName="col-span-2"
                    />
                    
                </div>
            </CardContent>
        </Card>
    )
}

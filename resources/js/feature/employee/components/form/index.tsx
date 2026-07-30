import { RHF } from "@/components/rhf-fields";
import { EmployeeHero } from "./fields/hero";
import { useEmployeeForm } from "./hooks/useEmployeeForm";
import { PersonSection } from "./fields/person-section";
import { ContractTypeSection } from "./fields/contract-type-section";
import { OtherContracts } from "./other-contracts";
import { Button } from "@/components/ui/button";
import { useEmployeeContext } from "./hooks/useEmployeeContext";
import { Spinner } from "@/components/ui/spinner";
import { Employee } from "../../types";

type Props ={
    current?:Employee
}

export function EmployeeFormFields({current}:Props) {
    const {form,submit,isCreating,isLoading} = useEmployeeForm({currentEmployee:current})
    const {contracts} = useEmployeeContext()
    const alreadyAnotherContract = contracts.length > 0
    return (
        <RHF.Form form={form} onSubmit={submit} className="gap-4 flex flex-col">
            <EmployeeHero/>
            <PersonSection autoComplete={!current}/>
            <ContractTypeSection/>

            <OtherContracts contracts={current?.employments ?? contracts}/>

            <div className="w-full flex flex-row justify-end">
                <Button disabled={isLoading} type="submit" className="p-4">
                    {
                        isLoading ? <Spinner /> : null
                    }
                    {
                        isCreating 
                            ? alreadyAnotherContract ? 'Adicionar contratacao' : 'Contratar'
                            :  'Editar contratacao'
                    }
                </Button>
            </div>
        </RHF.Form>
    )
}
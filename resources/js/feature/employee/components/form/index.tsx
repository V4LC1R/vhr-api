import { RHF } from "@/components/rhf-fields";
import { EmployeeHero } from "./fields/hero";
import { useEmployeeForm } from "./hooks/useEmployeeForm";
import { PersonSection } from "./fields/person-section";
import { ContractTypeSection } from "./fields/contract-type-section";
import { WorkloadSelect } from "./workload-select";

export function EmployeeFormFields() {
    const {form,submit} = useEmployeeForm({})

    return (
        <RHF.Form form={form} onSubmit={submit} className="gap-4 flex flex-col">
            <EmployeeHero/>
            <PersonSection/>
            <ContractTypeSection/>
        </RHF.Form>
    )
}
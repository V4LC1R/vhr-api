import { Loader2Icon } from "lucide-react"
import { Button } from "@/components/ui/button"
import { useEmployeeForm } from "../hooks/useEmployeeForm"
import { EmployeeHero } from "../components/create/hero"
import { PersonSection } from "../components/create/person-section"
import { ContractTypeSection } from "../components/create/contract-type-section"
import { WorkloadSection } from "../components/create/workload-section"

export function EmployeeNewView() {
    const {
        name,
        registerNumber,
        isLoadingRegisterNumber,

        cpf,
        setCpf,
        setName,
        email,
        setEmail,
        cellphone,
        setCellphone,
        matchedPerson,
        handleMatch,
        personErrors,

        selectedWorkloadId,
        selectWorkload,
        workloadIdError,

        kind,
        selectKind,
        kindError,

        submit,
        isSubmitting,
    } = useEmployeeForm()

    return (
        <div className="flex flex-col gap-4 pb-6">
            <EmployeeHero name={name} registerNumber={registerNumber} isLoadingRegisterNumber={isLoadingRegisterNumber} />

            <PersonSection
                cpf={cpf}
                onCpfChange={setCpf}
                name={name}
                onNameChange={setName}
                email={email}
                onEmailChange={setEmail}
                cellphone={cellphone}
                onCellphoneChange={setCellphone}
                matchedPerson={matchedPerson}
                onMatch={handleMatch}
                errors={personErrors}
            />

            <ContractTypeSection value={kind} onChange={selectKind} error={kindError} />

            <WorkloadSection
                selectedWorkloadId={selectedWorkloadId}
                onSelectWorkload={selectWorkload}
                workloadIdError={workloadIdError}
            />

            <div className="flex justify-end">
                <Button disabled={isSubmitting} onClick={submit} className="min-w-40">
                    {isSubmitting ? <Loader2Icon className="animate-spin" /> : "Cadastrar colaborador"}
                </Button>
            </div>
        </div>
    )
}

import { Loader2Icon } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Separator } from "@/components/ui/separator"
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
        pixKey,
        setPixKey,
        matchedPerson,
        handleMatch,
        personErrors,

        selectedWorkloadId,
        selectWorkload,
        workloadIdError,

        kind,
        selectKind,
        kindError,

        isProbationary,
        setIsProbationary,

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
                pixKey={pixKey}
                onPixKeyChange={setPixKey}
                matchedPerson={matchedPerson}
                onMatch={handleMatch}
                errors={personErrors}
            />

            <Card>
                <CardHeader>
                    <CardTitle>Vínculo e jornada</CardTitle>
                </CardHeader>
                <CardContent className="flex flex-col gap-5">
                    <ContractTypeSection
                        value={kind}
                        onChange={selectKind}
                        error={kindError}
                        isProbationary={isProbationary}
                        onIsProbationaryChange={setIsProbationary}
                    />

                    <Separator />

                    <WorkloadSection
                        selectedWorkloadId={selectedWorkloadId}
                        onSelectWorkload={selectWorkload}
                        workloadIdError={workloadIdError}
                    />
                </CardContent>
            </Card>

            <div className="flex justify-end">
                <Button disabled={isSubmitting} onClick={submit} className="min-w-40">
                    {isSubmitting ? <Loader2Icon className="animate-spin" /> : "Cadastrar colaborador"}
                </Button>
            </div>
        </div>
    )
}

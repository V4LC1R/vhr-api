import { Loader2Icon } from "lucide-react"

import { Employee } from "@/types/employee/types"
import { EmploymentType } from "@/types/employment/types"
import { useEmployeeForm } from "@/feature/employee/hooks/useEmployeeForm"
import { PersonSearchCombobox } from "@/feature/person/components/person-search-combobox"
import { PersonSection } from "@/feature/employee/components/create/person-section"
import { ContractTypeSection } from "@/feature/employee/components/create/contract-type-section"
import { WorkloadSection } from "@/feature/employee/components/create/workload-section"
import { Button } from "@/components/ui/button"
import { Separator } from "@/components/ui/separator"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"

export const TEMPORARY_KINDS: EmploymentType[] = ["dayli", "temporary", "freelancer"]

interface HireTemporaryDialogProps {
    open: boolean
    onOpenChange: (open: boolean) => void
    /** Recebe o colaborador recém-contratado (com activeEmployment.workload). */
    onHired: (employee: Employee) => void
}

/**
 * Contratação rápida de temporário sem sair da tela de lançamentos:
 * o CPF busca quem já foi contratado (autopreenche) ou cadastra a pessoa nova.
 */
export function HireTemporaryDialog({ open, onOpenChange, onHired }: HireTemporaryDialogProps) {
    const form = useEmployeeForm({
        onSuccess: (employee) => {
            onOpenChange(false)
            onHired(employee)
        },
    })

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-4xl">
                <DialogHeader>
                    <DialogTitle>Contratar temporário</DialogTitle>
                    <DialogDescription>
                        Informe o CPF: quem já passou pela empresa é preenchido automaticamente;
                        pessoa nova é cadastrada junto com a contratação.
                    </DialogDescription>
                </DialogHeader>

                <div className="flex flex-col gap-4">
                    <div className="flex flex-col gap-2 rounded-lg border bg-muted/30 p-3">
                        <span className="text-sm font-medium">Já passou pela empresa?</span>
                        <PersonSearchCombobox
                            className="w-full"
                            placeholder="Buscar contratado por nome..."
                            onSelect={(person) => {
                                form.handleMatch(person)
                                // Preenche o CPF junto — o lookup do PersonSection reconfirma o match.
                                if (person.cpf) form.setCpf(person.cpf)
                            }}
                        />
                    </div>

                    <PersonSection
                        cpf={form.cpf}
                        onCpfChange={form.setCpf}
                        name={form.name}
                        onNameChange={form.setName}
                        email={form.email}
                        onEmailChange={form.setEmail}
                        cellphone={form.cellphone}
                        onCellphoneChange={form.setCellphone}
                        pixKey={form.pixKey}
                        onPixKeyChange={form.setPixKey}
                        matchedPerson={form.matchedPerson}
                        onMatch={form.handleMatch}
                        errors={form.personErrors}
                    />

                    <ContractTypeSection
                        value={form.kind}
                        onChange={form.selectKind}
                        error={form.kindError}
                        kinds={TEMPORARY_KINDS}
                        showProbationary={false}
                        isProbationary={form.isProbationary}
                        onIsProbationaryChange={form.setIsProbationary}
                    />

                    <Separator />

                    <WorkloadSection
                        selectedWorkloadId={form.selectedWorkloadId}
                        onSelectWorkload={form.selectWorkload}
                        workloadIdError={form.workloadIdError}
                    />
                </div>

                <DialogFooter>
                    <DialogClose render={<Button variant="outline" />}>Cancelar</DialogClose>
                    <Button disabled={form.isSubmitting} onClick={form.submit} className="min-w-36">
                        {form.isSubmitting ? (
                            <Loader2Icon className="animate-spin" />
                        ) : (
                            "Contratar"
                        )}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

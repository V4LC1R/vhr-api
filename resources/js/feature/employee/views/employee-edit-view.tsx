import { Loader2Icon, SaveIcon, UserIcon, UserXIcon } from "lucide-react"

import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Separator } from "@/components/ui/separator"
import { ConfirmDialog } from "@/components/confirm-dialog"
import { useEmployeeEditForm } from "../hooks/useEmployeeEditForm"
import { PersonEditSection } from "../components/edit/person-edit-section"
import { ContractTypeSection } from "../components/create/contract-type-section"
import { WorkloadSection } from "../components/create/workload-section"
import { EMPLOYMENT_STATUS_LABELS, EMPLOYMENT_STATUS_VARIANTS } from "../components/table/columns"

interface EmployeeEditViewProps {
    employeeId: string
}

export function EmployeeEditView({ employeeId }: EmployeeEditViewProps) {
    const {
        employee,
        isLoading,
        isDismissed,

        cpf,
        name,
        setName,
        email,
        setEmail,
        cellphone,
        setCellphone,
        pixKey,
        setPixKey,
        personErrors,

        selectedWorkloadId,
        selectWorkload,
        workloadIdError,

        isProbationary,
        setIsProbationary,

        kind,
        selectKind,
        kindError,

        dismissDialogOpen,
        setDismissDialogOpen,
        confirmDismiss,
        isDismissing,

        submit,
        isSubmitting,
    } = useEmployeeEditForm(employeeId)

    if (isLoading) {
        return (
            <div className="flex flex-1 items-center justify-center py-20">
                <Loader2Icon className="size-6 animate-spin text-muted-foreground" />
            </div>
        )
    }

    if (!employee) {
        return (
            <div className="flex flex-1 items-center justify-center py-20 text-muted-foreground">
                Colaborador não encontrado.
            </div>
        )
    }

    const status = employee.activeEmployment?.status

    return (
        <div className="flex flex-col gap-4 pb-6">
            <div className="flex flex-col gap-4 rounded-xl border bg-card p-6 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-4">
                    <div className="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <UserIcon className="size-6" />
                    </div>
                    <div className="flex flex-col gap-1">
                        <span className="text-xs text-muted-foreground">Colaborador</span>
                        <span className="text-2xl font-semibold tracking-tight">{employee.person?.name}</span>
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    <Badge variant="secondary" className="text-sm">
                        Nº {employee.registerNumber}
                    </Badge>
                    {status && (
                        <Badge variant={EMPLOYMENT_STATUS_VARIANTS[status]}>
                            {EMPLOYMENT_STATUS_LABELS[status]}
                        </Badge>
                    )}
                    {isDismissed && <Badge variant="outline">Desligado</Badge>}
                </div>
            </div>

            <Card>
                <CardContent className="pt-6">
                    <PersonEditSection
                        cpf={cpf}
                        name={name}
                        onNameChange={setName}
                        email={email}
                        onEmailChange={setEmail}
                        cellphone={cellphone}
                        onCellphoneChange={setCellphone}
                        pixKey={pixKey}
                        onPixKeyChange={setPixKey}
                        errors={personErrors}
                    />
                </CardContent>
            </Card>

            {isDismissed ? (
                <Card>
                    <CardContent className="pt-6 text-sm text-muted-foreground">
                        Este colaborador está desligado. Vínculo e jornada não podem mais ser editados.
                    </CardContent>
                </Card>
            ) : (
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
            )}

            <div className="flex items-center justify-between">
                {!isDismissed ? (
                    <Button variant="destructive" onClick={() => setDismissDialogOpen(true)}>
                        <UserXIcon />
                        Desligar colaborador
                    </Button>
                ) : (
                    <span />
                )}

                {!isDismissed && (
                    <Button disabled={isSubmitting} onClick={submit} className="min-w-40">
                        {isSubmitting ? <Loader2Icon className="animate-spin" /> : <SaveIcon />}
                        {isSubmitting ? "Salvando..." : "Salvar alterações"}
                    </Button>
                )}
            </div>

            <ConfirmDialog
                open={dismissDialogOpen}
                onOpenChange={setDismissDialogOpen}
                title="Desligar colaborador"
                description={`Tem certeza que deseja desligar ${employee.person?.name}? Essa ação encerra o vínculo atual.`}
                confirmLabel="Desligar"
                confirmIcon={UserXIcon}
                destructive
                isLoading={isDismissing}
                onConfirm={confirmDismiss}
            />
        </div>
    )
}

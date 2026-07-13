import { useState } from "react"
import { startOfMonth } from "date-fns"
import { CalendarClockIcon, Loader2Icon, UserPlusIcon, ZapIcon } from "lucide-react"

import { ConfirmDialog } from "@/components/confirm-dialog"
import { Button } from "@/components/ui/button"
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs"
import { Employee } from "@/types/employee/types"
import { EmploymentType } from "@/types/employment/types"
import { EmployeeSelect } from "../components/employee-select/employee-select"
import { MonthPicker } from "../components/month-picker"
import { ExceptionDialog } from "../components/exception-dialog/exception-dialog"
import { useExceptionDialog } from "../components/exception-dialog/useExceptionDialog"
import {
    HireTemporaryDialog,
    TEMPORARY_KINDS,
} from "../components/temporaries/hire-temporary-dialog"
import { TimesheetTable } from "../components/timesheet/timesheet-table"
import { useTimesheet } from "../components/timesheet/useTimesheet"

/** Mesma divisão da tela de aprovações: CLTs de um lado, temporários do outro. */
type KindTab = "clt" | "temps"

const KIND_TAB_LABELS: Record<KindTab, string> = {
    clt: "CLTs",
    temps: "Temporários",
}

const KIND_TAB_KINDS: Record<KindTab, EmploymentType[]> = {
    clt: ["clt"],
    temps: TEMPORARY_KINDS,
}

export function TimesheetView() {
    const [kindTab, setKindTab] = useState<KindTab>("clt")
    const [employee, setEmployee] = useState<Employee | null>(null)
    const [monthDate, setMonthDate] = useState<Date>(() => startOfMonth(new Date()))
    const [hireOpen, setHireOpen] = useState(false)

    const employeeId = employee?.id ?? null
    const isTemps = kindTab === "temps"

    const timesheet = useTimesheet(employee, monthDate)
    const exceptionDialog = useExceptionDialog({ employeeId, onSaved: timesheet.refresh })

    function changeTab(tab: KindTab) {
        setKindTab(tab)
        // O colaborador selecionado pertence ao outro grupo.
        setEmployee(null)
    }

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <div className="flex flex-wrap items-center gap-2">
                    <Tabs value={kindTab} onValueChange={(value) => changeTab(value as KindTab)}>
                        <TabsList>
                            {(Object.keys(KIND_TAB_LABELS) as KindTab[]).map((tab) => (
                                <TabsTrigger key={tab} value={tab}>
                                    {KIND_TAB_LABELS[tab]}
                                </TabsTrigger>
                            ))}
                        </TabsList>
                    </Tabs>

                    {/* key remonta o combobox na troca de aba (limpa texto e lista). */}
                    <EmployeeSelect
                        key={kindTab}
                        value={employee}
                        onChange={setEmployee}
                        kinds={KIND_TAB_KINDS[kindTab]}
                    />

                    {isTemps && (
                        <Button variant="outline" onClick={() => setHireOpen(true)}>
                            <UserPlusIcon />
                            Contratar
                        </Button>
                    )}
                </div>

                <MonthPicker value={monthDate} onChange={setMonthDate} disabled={!employeeId} />
            </div>

            {!employeeId ? (
                <div className="flex flex-1 flex-col items-center justify-center gap-3 rounded-xl border bg-card py-20 text-muted-foreground">
                    <CalendarClockIcon className="size-8" />
                    <span>
                        {isTemps
                            ? "Selecione um temporário (diarista, freelancer ou temporário)."
                            : "Selecione um colaborador pra lançar o ponto."}
                    </span>
                </div>
            ) : timesheet.isInitialLoading ? (
                <div className="flex flex-1 items-center justify-center rounded-xl border bg-card py-20">
                    <Loader2Icon className="size-6 animate-spin text-muted-foreground" />
                </div>
            ) : (
                <TimesheetTable
                    registerDate={timesheet.registerDate}
                    monthDate={monthDate}
                    employmentKind={employee?.activeEmployment?.kind}
                    daysByDate={timesheet.daysByDate}
                    isLoading={timesheet.isLoadingDays}
                    isSaving={timesheet.isSaving}
                    canFullDay={timesheet.canFullDay}
                    onAddPunch={timesheet.addPunch}
                    onUpdatePunchTime={timesheet.updatePunchTime}
                    onTogglePunchType={timesheet.togglePunchType}
                    onDeletePunch={timesheet.deletePunch}
                    onFullDay={timesheet.fullDay}
                    onEditDayType={exceptionDialog.openFor}
                    onChangeDayType={timesheet.changeDayType}
                    onSubmitDay={timesheet.submitDay}
                />
            )}

            <ConfirmDialog
                open={!!timesheet.fullDayToReplace}
                onOpenChange={(open) => !open && timesheet.cancelFullDayReplace()}
                title={isTemps ? "Lançar presença" : "Lançar dia completo"}
                description="O dia já tem marcações — elas serão substituídas pelos horários da jornada."
                confirmLabel="Substituir"
                confirmIcon={ZapIcon}
                isLoading={timesheet.isCreatingBatch}
                onConfirm={timesheet.confirmFullDayReplace}
            />

            <ExceptionDialog
                open={exceptionDialog.open}
                onOpenChange={exceptionDialog.setOpen}
                description={exceptionDialog.description}
                defaultValues={exceptionDialog.defaultValues}
                isSubmitting={exceptionDialog.isSubmitting}
                onSubmit={exceptionDialog.submit}
            />

            {/* Montado só quando aberto: zera o formulário e evita fetch do nº de matrícula à toa. */}
            {hireOpen && (
                <HireTemporaryDialog
                    open={hireOpen}
                    onOpenChange={setHireOpen}
                    onHired={setEmployee}
                />
            )}
        </div>
    )
}

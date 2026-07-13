import { useState } from "react"
import { startOfMonth } from "date-fns"
import { CalendarClockIcon, Loader2Icon, ZapIcon } from "lucide-react"

import { ConfirmDialog } from "@/components/confirm-dialog"
import { Employee } from "@/types/employee/types"
import { EmployeeSelect } from "../components/employee-select/employee-select"
import { MonthPicker } from "../components/month-picker"
import { ExceptionDialog } from "../components/exception-dialog/exception-dialog"
import { useExceptionDialog } from "../components/exception-dialog/useExceptionDialog"
import { TimesheetTable } from "../components/timesheet/timesheet-table"
import { useTimesheet } from "../components/timesheet/useTimesheet"

export function TimesheetView() {
    const [employee, setEmployee] = useState<Employee | null>(null)
    const [monthDate, setMonthDate] = useState<Date>(() => startOfMonth(new Date()))

    const employeeId = employee?.id ?? null

    const timesheet = useTimesheet(employee, monthDate)
    const exceptionDialog = useExceptionDialog({ employeeId, onSaved: timesheet.refresh })

    return (
        <div className="flex min-h-0 flex-1 flex-col gap-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <EmployeeSelect value={employee} onChange={setEmployee} />
                <MonthPicker value={monthDate} onChange={setMonthDate} disabled={!employeeId} />
            </div>

            {!employeeId ? (
                <div className="flex flex-1 flex-col items-center justify-center gap-3 rounded-xl border bg-card py-20 text-muted-foreground">
                    <CalendarClockIcon className="size-8" />
                    <span>Selecione um colaborador pra lançar o ponto.</span>
                </div>
            ) : timesheet.isInitialLoading ? (
                <div className="flex flex-1 items-center justify-center rounded-xl border bg-card py-20">
                    <Loader2Icon className="size-6 animate-spin text-muted-foreground" />
                </div>
            ) : (
                <TimesheetTable
                    registerDate={timesheet.registerDate}
                    monthDate={monthDate}
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
                title="Lançar dia completo"
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
        </div>
    )
}

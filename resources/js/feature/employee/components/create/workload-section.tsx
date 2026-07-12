import * as React from "react"
import { CalendarClockIcon, Loader2Icon, PlusIcon } from "lucide-react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { Workload } from "@/feature/workload/types/types"
import { WorkloadPayload } from "@/feature/workload/types/schemas"
import { useListWorkload } from "@/feature/workload/hooks/useListWorkload"
import { useCreateWorkload } from "@/feature/workload/hooks/useCreateWorkload"
import { WorkloadFormDialog } from "@/feature/workload/components/workload-form-dialog"
import { Button } from "@/components/ui/button"
import { FieldError, FieldLegend } from "@/components/ui/field"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectItemContent,
    SelectItemDescription,
    SelectItemIcon,
    SelectItemTitle,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"

interface WorkloadSectionProps {
    selectedWorkloadId: string | null
    onSelectWorkload: (workload: Workload) => void
    workloadIdError?: string
}

function formatTime(time: string) {
    return time.slice(0, 5)
}

export function WorkloadSection({ selectedWorkloadId, onSelectWorkload, workloadIdError }: WorkloadSectionProps) {
    const [open, setOpen] = React.useState(false)
    const { list, data: workloads, isLoadingWorkloads } = useListWorkload({ per_page: 50 })
    const { create: createWorkload, isCreatingWorkload } = useCreateWorkload()

    React.useEffect(() => {
        list()
    }, [])

    const selectedWorkload = workloads?.find((w) => w.id === selectedWorkloadId)

    async function handleCreate(values: WorkloadPayload) {
        try {
            const workload = await createWorkload(values)
            await list()
            onSelectWorkload(workload)
            setOpen(false)
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível cadastrar a jornada."))
        }
    }

    return (
        <div className="flex flex-col gap-3">
            <div className="flex flex-row items-center justify-between">
                <FieldLegend variant="label">Jornada</FieldLegend>
                <Button variant="outline" size="sm" onClick={() => setOpen(true)}>
                    <PlusIcon />
                    Nova jornada
                </Button>
                <WorkloadFormDialog
                    open={open}
                    onOpenChange={setOpen}
                    title="Cadastrar jornada"
                    description="Crie uma nova jornada de trabalho pra empresa."
                    submitLabel="Cadastrar jornada"
                    submittingLabel="Cadastrando..."
                    isSubmitting={isCreatingWorkload}
                    onSubmit={handleCreate}
                />
            </div>
            <div className="flex flex-col gap-3">
                <Select
                    items={Object.fromEntries((workloads ?? []).map((w) => [w.id, w.description]))}
                    value={selectedWorkloadId}
                    onValueChange={(id) => {
                        const workload = workloads?.find((w) => w.id === id)
                        if (workload) onSelectWorkload(workload)
                    }}
                >
                    <SelectTrigger className="w-full" disabled={isLoadingWorkloads}>
                        <SelectValue
                            placeholder={isLoadingWorkloads ? "Carregando jornadas..." : "Selecione uma jornada"}
                        />
                        {isLoadingWorkloads && (
                            <Loader2Icon className="size-4 shrink-0 animate-spin text-muted-foreground" />
                        )}
                    </SelectTrigger>
                    <SelectContent>
                        {(workloads ?? []).map((workload) => (
                            <SelectItem key={workload.id} value={workload.id} className="py-2">
                                <SelectItemIcon>
                                    <CalendarClockIcon />
                                </SelectItemIcon>
                                <SelectItemContent>
                                    <SelectItemTitle>{workload.description}</SelectItemTitle>
                                    <SelectItemDescription>
                                        {workload.weeklyHours}h/semana · {formatTime(workload.entryTime)} às{" "}
                                        {formatTime(workload.leftTime)}
                                    </SelectItemDescription>
                                </SelectItemContent>
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                <FieldError errors={workloadIdError ? [{ message: workloadIdError }] : undefined} />

                {selectedWorkload && (
                    <div className="grid grid-cols-2 gap-4 rounded-lg border bg-muted/30 p-4 text-sm sm:grid-cols-3">
                        <div className="flex flex-col gap-0.5">
                            <span className="text-xs text-muted-foreground">Carga horária</span>
                            <span>
                                {selectedWorkload.weeklyHours}h/semana · {selectedWorkload.monthlyHours}h/mês
                            </span>
                        </div>
                        <div className="flex flex-col gap-0.5">
                            <span className="text-xs text-muted-foreground">Horário</span>
                            <span>
                                {formatTime(selectedWorkload.entryTime)} às {formatTime(selectedWorkload.leftTime)}
                            </span>
                        </div>
                        <div className="flex flex-col gap-0.5">
                            <span className="text-xs text-muted-foreground">Intervalo</span>
                            <span>
                                {selectedWorkload.interval.startAt && selectedWorkload.interval.endAt
                                    ? `${formatTime(selectedWorkload.interval.startAt)} às ${formatTime(selectedWorkload.interval.endAt)}`
                                    : "—"}
                            </span>
                        </div>
                    </div>
                )}
            </div>
        </div>
    )
}

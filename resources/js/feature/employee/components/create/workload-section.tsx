import * as React from "react"
import { useForm } from "react-hook-form"
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema"
import { CalendarClockIcon, PlusIcon } from "lucide-react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { Workload } from "@/feature/workload/types/types"
import { workloadSchema, WorkloadPayload } from "@/feature/workload/types/schemas"
import { useListWorkload } from "@/feature/workload/hooks/useListWorkload"
import { useCreateWorkload } from "@/feature/workload/hooks/useCreateWorkload"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { RHF } from "@/components/rhf-fields"
import { FieldError, FieldGroup } from "@/components/ui/field"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/components/ui/dialog"
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

const emptyWorkload: WorkloadPayload = {
    description: "",
    monthlyHours: 0,
    weeklyHours: 0,
    entryTime: "",
    leftTime: "",
    intervalStartAt: "",
    intervalEndAt: "",
}

export function WorkloadSection({ selectedWorkloadId, onSelectWorkload, workloadIdError }: WorkloadSectionProps) {
    const [open, setOpen] = React.useState(false)
    const { list, data: workloads } = useListWorkload({ per_page: 50 })
    const { create: createWorkload, isCreatingWorkload } = useCreateWorkload()

    const form = useForm<WorkloadPayload>({
        resolver: standardSchemaResolver(workloadSchema),
        defaultValues: emptyWorkload,
    })

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
            form.reset(emptyWorkload)
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível cadastrar a jornada."))
        }
    }

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>Jornada</CardTitle>
                <Dialog
                    open={open}
                    onOpenChange={(next) => {
                        setOpen(next)
                        if (!next) form.reset(emptyWorkload)
                    }}
                >
                    <DialogTrigger render={<Button variant="outline" size="sm" />}>
                        <PlusIcon />
                        Nova jornada
                    </DialogTrigger>
                    <DialogContent className="sm:max-w-lg">
                        <DialogHeader>
                            <DialogTitle>Cadastrar jornada</DialogTitle>
                            <DialogDescription>
                                Crie uma nova jornada de trabalho pra empresa.
                            </DialogDescription>
                        </DialogHeader>

                        <FieldGroup>
                            <RHF.Input
                                name="description"
                                label="Descrição"
                                control={form.control}
                                placeholder="Jornada 44h (Seg-Sex 08-18)"
                            />
                            <div className="grid grid-cols-2 gap-4">
                                <RHF.Input
                                    name="weeklyHours"
                                    label="Horas semanais"
                                    type="number"
                                    control={form.control}
                                />
                                <RHF.Input
                                    name="monthlyHours"
                                    label="Horas mensais"
                                    type="number"
                                    control={form.control}
                                />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <RHF.Input name="entryTime" label="Entrada" type="time" step={1} control={form.control} />
                                <RHF.Input name="leftTime" label="Saída" type="time" step={1} control={form.control} />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <RHF.Input
                                    name="intervalStartAt"
                                    label="Início do intervalo"
                                    type="time"
                                    step={1}
                                    control={form.control}
                                />
                                <RHF.Input
                                    name="intervalEndAt"
                                    label="Fim do intervalo"
                                    type="time"
                                    step={1}
                                    control={form.control}
                                />
                            </div>
                        </FieldGroup>

                        <DialogFooter>
                            <DialogClose render={<Button variant="outline" />}>Cancelar</DialogClose>
                            <Button disabled={isCreatingWorkload} onClick={form.handleSubmit(handleCreate)}>
                                {isCreatingWorkload ? "Cadastrando..." : "Cadastrar jornada"}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </CardHeader>
            <CardContent className="flex flex-col gap-3">
                <Select
                    items={Object.fromEntries((workloads ?? []).map((w) => [w.id, w.description]))}
                    value={selectedWorkloadId}
                    onValueChange={(id) => {
                        const workload = workloads?.find((w) => w.id === id)
                        if (workload) onSelectWorkload(workload)
                    }}
                >
                    <SelectTrigger className="w-full">
                        <SelectValue placeholder="Selecione uma jornada" />
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
            </CardContent>
        </Card>
    )
}

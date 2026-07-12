import * as React from "react"
import { Controller, useForm } from "react-hook-form"
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema"
import { CalendarClockIcon, Loader2Icon, MinusIcon, PlusIcon } from "lucide-react"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { Workload } from "@/feature/workload/types/types"
import { workloadSchema, WorkloadPayload } from "@/feature/workload/types/schemas"
import { useListWorkload } from "@/feature/workload/hooks/useListWorkload"
import { useCreateWorkload } from "@/feature/workload/hooks/useCreateWorkload"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { RHF } from "@/components/rhf-fields"
import { Field, FieldError, FieldGroup, FieldLabel, FieldLegend } from "@/components/ui/field"
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

const DEFAULT_WEEKLY_HOURS = 40
const WEEKLY_HOURS_PRESETS = [20, 30, 36, 40, 44]

/** Convenção usual no Brasil pra equivalência semanal → mensal (ex: 40h → 200h, 44h → 220h). */
function monthlyHoursFromWeekly(weeklyHours: number) {
    return weeklyHours * 5
}

interface HoursStepperProps {
    id: string
    value: number
    onChange: (value: number) => void
    min?: number
    max?: number
}

function HoursStepper({ id, value, onChange, min = 0, max = 744 }: HoursStepperProps) {
    return (
        <div className="flex items-center gap-1.5">
            <Button
                type="button"
                variant="outline"
                size="icon-sm"
                onClick={() => onChange(Math.max(min, value - 1))}
                disabled={value <= min}
                aria-label="Diminuir"
            >
                <MinusIcon />
            </Button>
            <Input
                id={id}
                type="number"
                value={value}
                onChange={(e) => onChange(Number(e.target.value))}
                className="text-center"
            />
            <Button
                type="button"
                variant="outline"
                size="icon-sm"
                onClick={() => onChange(Math.min(max, value + 1))}
                disabled={value >= max}
                aria-label="Aumentar"
            >
                <PlusIcon />
            </Button>
        </div>
    )
}

const emptyWorkload: WorkloadPayload = {
    description: "",
    monthlyHours: monthlyHoursFromWeekly(DEFAULT_WEEKLY_HOURS),
    weeklyHours: DEFAULT_WEEKLY_HOURS,
    entryTime: "",
    leftTime: "",
    intervalStartAt: "",
    intervalEndAt: "",
}

export function WorkloadSection({ selectedWorkloadId, onSelectWorkload, workloadIdError }: WorkloadSectionProps) {
    const [open, setOpen] = React.useState(false)
    const { list, data: workloads, isLoadingWorkloads } = useListWorkload({ per_page: 50 })
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
        <div className="flex flex-col gap-3">
            <div className="flex flex-row items-center justify-between">
                <FieldLegend variant="label">Jornada</FieldLegend>
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
                            <div className="flex flex-wrap gap-1.5">
                                {WEEKLY_HOURS_PRESETS.map((preset) => (
                                    <Button
                                        key={preset}
                                        type="button"
                                        variant={form.watch("weeklyHours") === preset ? "secondary" : "outline"}
                                        size="sm"
                                        onClick={() => {
                                            form.setValue("weeklyHours", preset, { shouldValidate: true })
                                            form.setValue("monthlyHours", monthlyHoursFromWeekly(preset), {
                                                shouldValidate: true,
                                            })
                                        }}
                                    >
                                        {preset}h
                                    </Button>
                                ))}
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <Controller
                                    control={form.control}
                                    name="weeklyHours"
                                    render={({ field, fieldState }) => (
                                        <Field data-invalid={fieldState.invalid}>
                                            <FieldLabel htmlFor="weeklyHours">Horas semanais</FieldLabel>
                                            <HoursStepper
                                                id="weeklyHours"
                                                max={44}
                                                value={field.value}
                                                onChange={(weeklyHours) => {
                                                    field.onChange(weeklyHours)
                                                    form.setValue("monthlyHours", monthlyHoursFromWeekly(weeklyHours), {
                                                        shouldValidate: true,
                                                    })
                                                }}
                                            />
                                            <FieldError errors={fieldState.error ? [fieldState.error] : undefined} />
                                        </Field>
                                    )}
                                />
                                <Controller
                                    control={form.control}
                                    name="monthlyHours"
                                    render={({ field, fieldState }) => (
                                        <Field data-invalid={fieldState.invalid}>
                                            <FieldLabel htmlFor="monthlyHours">Horas mensais</FieldLabel>
                                            <HoursStepper id="monthlyHours" value={field.value} onChange={field.onChange} />
                                            <FieldError errors={fieldState.error ? [fieldState.error] : undefined} />
                                        </Field>
                                    )}
                                />
                            </div>
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <RHF.TimePicker
                                    name="entryTime"
                                    label="Entrada"
                                    control={form.control}
                                    stepMinutes={15}
                                    format={(v) => (v ? String(v).slice(0, 5) : null)}
                                    parse={(t) => (t ? `${t}:00` : "")}
                                />
                                <RHF.TimePicker
                                    name="leftTime"
                                    label="Saída"
                                    control={form.control}
                                    stepMinutes={15}
                                    format={(v) => (v ? String(v).slice(0, 5) : null)}
                                    parse={(t) => (t ? `${t}:00` : "")}
                                />
                            </div>
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <RHF.TimePicker
                                    name="intervalStartAt"
                                    label="Início do intervalo"
                                    control={form.control}
                                    stepMinutes={15}
                                    format={(v) => (v ? String(v).slice(0, 5) : null)}
                                    parse={(t) => (t ? `${t}:00` : "")}
                                />
                                <RHF.TimePicker
                                    name="intervalEndAt"
                                    label="Fim do intervalo"
                                    control={form.control}
                                    stepMinutes={15}
                                    format={(v) => (v ? String(v).slice(0, 5) : null)}
                                    parse={(t) => (t ? `${t}:00` : "")}
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

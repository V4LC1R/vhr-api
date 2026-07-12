import * as React from "react"
import { Controller, useForm } from "react-hook-form"
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema"
import { MinusIcon, PlusIcon } from "lucide-react"

import { workloadSchema, WorkloadPayload } from "../types/schemas"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { RHF } from "@/components/rhf-fields"
import { Field, FieldError, FieldGroup, FieldLabel } from "@/components/ui/field"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"

const DEFAULT_WEEKLY_HOURS = 40
const WEEKLY_HOURS_PRESETS = [20, 30, 36, 40, 44]

/** Convenção usual no Brasil pra equivalência semanal → mensal (ex: 40h → 200h, 44h → 220h). */
function monthlyHoursFromWeekly(weeklyHours: number) {
    return weeklyHours * 5
}

export const emptyWorkloadForm: WorkloadPayload = {
    description: "",
    monthlyHours: monthlyHoursFromWeekly(DEFAULT_WEEKLY_HOURS),
    weeklyHours: DEFAULT_WEEKLY_HOURS,
    entryTime: "",
    leftTime: "",
    intervalStartAt: "",
    intervalEndAt: "",
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

interface WorkloadFormDialogProps {
    open: boolean
    onOpenChange: (open: boolean) => void
    title: string
    description?: string
    submitLabel: string
    submittingLabel: string
    defaultValues?: WorkloadPayload
    isSubmitting?: boolean
    onSubmit: (values: WorkloadPayload) => Promise<void> | void
}

export function WorkloadFormDialog({
    open,
    onOpenChange,
    title,
    description,
    submitLabel,
    submittingLabel,
    defaultValues,
    isSubmitting,
    onSubmit,
}: WorkloadFormDialogProps) {
    const form = useForm<WorkloadPayload>({
        resolver: standardSchemaResolver(workloadSchema),
        defaultValues: defaultValues ?? emptyWorkloadForm,
    })

    // Reseta só na abertura — resetar em qualquer re-render do pai perderia o
    // que o usuário já digitou.
    React.useEffect(() => {
        if (open) form.reset(defaultValues ?? emptyWorkloadForm)
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open])

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description && <DialogDescription>{description}</DialogDescription>}
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
                    <Button disabled={isSubmitting} onClick={form.handleSubmit(onSubmit)}>
                        {isSubmitting ? submittingLabel : submitLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

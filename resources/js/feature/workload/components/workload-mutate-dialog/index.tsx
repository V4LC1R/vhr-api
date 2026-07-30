import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Workload } from "@/feature/workload/types/types";
import { Icon } from "@iconify/react";
import { useWorkloadMutateForm } from "./hooks/useWorkloadMutateForm";
import { Field, FieldError, FieldGroup } from "@/components/ui/field";
import { Spinner } from "@/components/ui/spinner";
import { WeeklyHours } from "./weekly-hours";
import { UnityInput } from "./unity-input";

type Props = {
    current?:Workload
    open:boolean,
    onSaved?:(workload:Workload)=>void
    toggle:()=>void
}

const formatTime = (value?: string | null) => (value ? value.slice(0, 5) : null)
const parseTime = (time: string | null) => (time ? `${time}:00` : '')

export function WorkloadMutateDialog({
    open,
    toggle,
    current,
    onSaved
}:Props) {

    const {
        isCreate,
        isLoading,
        form,
        onSubmit
    } = useWorkloadMutateForm({
        current,
        onSaved:(w)=>{
            toggle()
            onSaved?.(w)
        }
    })

    const title = isCreate
        ? 'Nova jornada'
        : 'Editar Jornada'

    const subTitle = isCreate
        ? 'Reutilizável no cadastro de colaboradores'
        : current?.description

    return (
        <Dialog
            open={open}
            onOpenChange={toggle}
        >
            <DialogContent className="sm:max-w-lg">
                <RHF.Form
                    form={form}
                    onSubmit={onSubmit}
                    className="contents"
                    stopPropagation
                >
                    <DialogHeader>
                        <DialogTitle className="font-semibold">{title}</DialogTitle>
                        <DialogDescription>{subTitle}</DialogDescription>
                    </DialogHeader>

                    <FieldGroup>
                        <RHF.Input
                            control={form.control}
                            label="Descrição"
                            name="description"
                        />

                        <Field data-invalid={!!form.formState.errors.weeklyHours}>
                            <WeeklyHours name="weeklyHours" />
                            <FieldError
                                errors={form.formState.errors.weeklyHours ? [form.formState.errors.weeklyHours] : undefined}
                            />
                        </Field>

                        <UnityInput
                            
                            
                            label="Horas Mensais"
                            name="monthlyHours"
                            
                        />

                        <div className="grid grid-cols-2 gap-2">
                            <RHF.TimePicker
                                control={form.control}
                                name="entryTime"
                                label="Entrada"
                                format={formatTime}
                                parse={parseTime}
                            />
                            <RHF.TimePicker
                                control={form.control}
                                name="leftTime"
                                label="Saida"
                                format={formatTime}
                                parse={parseTime}
                            />
                            <RHF.TimePicker
                                control={form.control}
                                name="intervalStartAt"
                                label="Início Intervalo"
                                format={formatTime}
                                parse={parseTime}
                            />
                            <RHF.TimePicker
                                control={form.control}
                                name="intervalEndAt"
                                label="Fim Intervalo"
                                format={formatTime}
                                parse={parseTime}
                            />
                        </div>

                    </FieldGroup>


                    <DialogFooter>
                        <Button onClick={toggle} variant="outline" className="p-4 font-light">
                            Cancelar
                        </Button>
                        <Button disabled={isLoading} type="submit" className="p-4">
                            {
                                isLoading
                                    ?<Spinner />
                                    :<Icon icon="solar:check-read-line-duotone"/>
                            }
                            Salvar Jornada
                        </Button>
                    </DialogFooter>
                </RHF.Form>
            </DialogContent>
        </Dialog>
    )
}

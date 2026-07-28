import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Workload } from "@/feature/workload/types/types";
import { Icon } from "@iconify/react";
import { useWorkloadMutateForm } from "./hooks/useWorkloadMutateForm";
import { Field, FieldError, FieldGroup } from "@/components/ui/field";
import { Spinner } from "@/components/ui/spinner";
import { WeeklyHours } from "./weekly-hours";
import { Controller } from "react-hook-form";

type Props = {
    current?:Workload
    open:boolean,
    onSaved?:(workload:Workload)=>void
    toggle:()=>void
}

export function WorkloadMutateDialog({
    open,
    toggle,
    current,
    onSaved
}:Props) {
    
    const {isCreate,isLoading,form,onSubmit} = useWorkloadMutateForm({current,onSaved})

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
                <RHF.Form form={form} onSubmit={onSubmit} className="contents">
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

                        <Controller
                            control={form.control}
                            name="weeklyHours"
                            render={({ field, fieldState }) => (
                                 <Field data-invalid={fieldState.invalid}>
                                    <WeeklyHours
                                        onChange={(w)=>field.onChange(w.value)}
                                        value={field.value}
                                    />
                                    <FieldError
                                       
                                        errors={fieldState.error ? [fieldState.error] : undefined}
                                    />
                                </Field>
                            )}
                        />

                        <RHF.Input
                            control={form.control}
                            label="Horas Mensais"
                            name="monthlyHours"
                            type="number"
                        />

                        <div className="grid grid-cols-2 gap-2">
                            <RHF.TimePicker 
                                control={form.control}
                                name="entryTime"
                                label="Entrada"
                            />
                            <RHF.TimePicker
                                control={form.control}
                                name="leftTime"
                                label="Saida"
                            />
                            <RHF.TimePicker
                                control={form.control}
                                name="intervalStartAt"
                                label="Início Intervalo"
                            />
                            <RHF.TimePicker
                                control={form.control}
                                name="intervalEndAt"
                                label="Fim Intervalo"
                            />
                        </div>

                    </FieldGroup>


                    <DialogFooter>
                        <Button onClick={toggle} variant="outline" className="p-4 font-light">
                            Cancelar
                        </Button>
                        <Button disabled={isLoading} className="p-4">
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
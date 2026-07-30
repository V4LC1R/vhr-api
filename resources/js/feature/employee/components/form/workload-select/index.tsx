import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";
import { SelectItemContent, SelectItemDescription, SelectItemIcon, SelectItemTitle } from "@/components/ui/select";
import { WorkloadMutateDialog } from "@/feature/workload/components/workload-mutate-dialog";
import { useListWorkload } from "@/feature/workload/hooks/useListWorkload";
import { Workload } from "@/feature/workload/types";
import { useBoolean } from "@/hooks/use-boolean";
import { Icon } from "@iconify/react";
import { useFormContext } from "react-hook-form";


export function WorkloadSelect() {
    const {control,setValue} = useFormContext()
    const {value,toggle,setTrue} = useBoolean()
    const { data: workloads } = useListWorkload({ per_page: 50 })

    function handleCreateWorload(workload:Workload) {
        setValue("workloadId",
            workload.id,
            {
                shouldDirty:true,
                shouldValidate:true
            }
        )
    }

    return (
        <>
            <WorkloadMutateDialog
                open={value}
                toggle={toggle}
                onSaved={handleCreateWorload}
            />
            <div className="w-full flex flex-row items-end gap-2 ">
                <RHF.Select
                    label="Jornada"
                    triggerClassName="py-6"
                    getOptionValue={(e)=>e.id}
                    itemRender={(val)=>(
                        <>
                            <SelectItemIcon className="flex flex-col items-center">
                                <Icon icon="solar:clock-square-bold-duotone"/>
                            </SelectItemIcon>
                            <SelectItemContent>
                                <SelectItemTitle>{val.description}</SelectItemTitle>
                                <SelectItemDescription>{val.entryTime} às {val.leftTime}</SelectItemDescription>
                            </SelectItemContent>
                        </>
                    )}
                    control={control}
                    options={workloads ?? []}
                    name="workloadId"
                />
                <Button 
                    onClick={()=>setTrue()} 
                    variant="outline" 
                    className="flex flex-row gap-1 p-5 py-6 border-dashed border-[#9A7B33] bg-background text-primary"
                >
                    <Icon icon="qlementine-icons:plus-16"/>
                    <span>Criar Jornada</span>
                </Button>
            </div>
        </>
    )
}
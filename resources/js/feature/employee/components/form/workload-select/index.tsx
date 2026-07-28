import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";
import { SelectItemContent, SelectItemDescription, SelectItemIcon, SelectItemTitle } from "@/components/ui/select";
import { WorkloadFormDialog } from "@/feature/workload/components/workload-form-dialog";
import { WorkloadMutateDialog } from "@/feature/workload/components/workload-mutate-dialog";
import { useCreateWorkload } from "@/feature/workload/hooks/useCreateWorkload";
import { useListWorkload } from "@/feature/workload/hooks/useListWorkload";
import { useBoolean } from "@/hooks/use-boolean";
import { Icon } from "@iconify/react";
import { useEffect } from "react";
import { useFormContext } from "react-hook-form";


type Props = {
    name:string
}

export function WorkloadSelect({name}:Props) {
    const {control} = useFormContext()
    const {value,toggle,setTrue} = useBoolean()
    const { list,data: workloads } = useListWorkload({ per_page: 50 })

    return (
        <>
            <WorkloadMutateDialog
                open={value}
                toggle={toggle}
                onSaved={()=>list()}
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
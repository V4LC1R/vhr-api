import { useCreateWorkload } from "@/feature/workload/hooks/useCreateWorkload";
import { useUpdateWorkload } from "@/feature/workload/hooks/useUpdateWorkload";
import { mapToApi, mapToForm, Workload, WorkloadPayload, workloadSchema } from "@/feature/workload/types";
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema"
import { useEffect, useMemo } from "react";
import { useForm, useWatch } from "react-hook-form";
import { toast } from "react-hot-toast";

type Props = {
    current?:Workload
    onSaved?:(data:Workload)=>void
}

export function useWorkloadMutateForm({current,onSaved}:Props) {

    const isCreate = current?.id === undefined

    const {create,isCreatingWorkload,response} = useCreateWorkload()
    const {update,isUpdatingWorkload} = useUpdateWorkload()

    const weeklyHours = useWatch({name:'weeklyHours'})

    const currentValue = useMemo(()=>{
        return current 
            ? mapToForm(current)
            : {}
    },[current])

    const form = useForm<WorkloadPayload>({
        defaultValues:currentValue,
        resolver:standardSchemaResolver(workloadSchema)
    })

    const onSubmit = async(data:WorkloadPayload) =>{
       
        try {
            const payload = mapToApi(data)

            if(isCreate) {
                const newWorkload = await create(payload)
                onSaved?.(newWorkload)
                return toast.success('Jornada Cadastrada com Sucesso!')
            }

            const updatedWorkload = await update(current.id,payload)
            onSaved?.(updatedWorkload)
            return toast.success('Jornada Editada com Sucesso!')
        } catch (error) {
            toast.error('Erro ao cadastrar Jornada!')
        }
    }

    return {
        isCreate,
        isLoading: isCreatingWorkload || isUpdatingWorkload,
        response,
        onSubmit,
        form
    }
}
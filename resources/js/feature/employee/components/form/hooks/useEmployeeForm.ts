import { Employee, EmployeePayload, employeeSchema, mapToApi, mapToForm, } from "@/feature/employee/types"

import { standardSchemaResolver } from "@hookform/resolvers/standard-schema"
import { useMemo } from "react"
import { useForm } from "react-hook-form"
import { defaultEmployeeValues } from "../constants/defaultValues"
import { useCreateEmployee } from "@/feature/employee/hooks/useCreateEmployee"
import { useUpdateEmployee } from "@/feature/employee/hooks/useUpdateEmployee"
import toast from "react-hot-toast"

type Props = {
    currentEmployee?:Omit<Employee, 'id'>
    id?:string
}

export function useEmployeeForm({currentEmployee,id}:Props) {
    const isCreating = !id

    const {create,isCreatingEmployee} = useCreateEmployee()
    const {update,isUpdatingEmployee} = useUpdateEmployee()

    const currentMemo = useMemo(()=>{
        return currentEmployee 
            ? mapToForm(currentEmployee)
            : defaultEmployeeValues
    },[currentEmployee])

    const form = useForm<EmployeePayload>({
        defaultValues:currentMemo,
        resolver:standardSchemaResolver(employeeSchema)
    })

    const onSubmit = async(data:EmployeePayload) =>{
       
        try {
            const payload = mapToApi(data)

            if(isCreating) {
                await create(payload)
                return toast.success('Colaborador Cadastrado com Sucesso!')
            }

            await update(id!,payload)
            return toast.success('Colaborador Editado com Sucesso!')
        } catch (error) {
            toast.error('Erro ao cadastrar Colaborador!')
        }
    }

    return{
        form,
        isLoading:isCreatingEmployee || isUpdatingEmployee,
        submit:onSubmit
    }
}
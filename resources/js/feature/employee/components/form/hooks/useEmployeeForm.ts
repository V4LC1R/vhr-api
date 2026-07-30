import { Employee, EmployeePayload, employeeSchema, mapToApi, mapToForm, } from "@/feature/employee/types"

import { standardSchemaResolver } from "@hookform/resolvers/standard-schema"
import { useEffect, useMemo } from "react"
import { useForm } from "react-hook-form"
import { defaultEmployeeValues } from "../constants/defaultValues"
import { useCreateEmployee } from "@/feature/employee/hooks/useCreateEmployee"
import { useUpdateEmployee } from "@/feature/employee/hooks/useUpdateEmployee"
import toast from "react-hot-toast"

type Props = {
    currentEmployee?:Employee
}

export function useEmployeeForm({currentEmployee}:Props) {
    const isCreating = !currentEmployee?.id

    const {create,isCreatingEmployee} = useCreateEmployee()
    const {update,isUpdatingEmployee} = useUpdateEmployee()

    const currentMemo = useMemo(()=>{
        console.log(currentEmployee)
        return currentEmployee 
            ? mapToForm(currentEmployee)
            : defaultEmployeeValues
    },[currentEmployee])

    const form = useForm<EmployeePayload>({
        values:currentMemo,
        resolver:standardSchemaResolver(employeeSchema)
    })

    const onSubmit = async(data:EmployeePayload) =>{

        try {
            const payload = mapToApi(data)

            if(isCreating) {
                await create(payload)
                return toast.success('Colaborador Cadastrado com Sucesso!')
            }

            await update(currentEmployee.id!,payload)
            return toast.success('Colaborador Editado com Sucesso!')
        } catch (error) {
            toast.error('Erro ao cadastrar Colaborador!')
        }
    }

    return{
        isCreating,
        form,
        isLoading:isCreatingEmployee || isUpdatingEmployee,
        submit:onSubmit
    }
}
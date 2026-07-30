import { EmployeePayload, formatCellphone } from "@/feature/employee/types";
import { parseBirthdate } from "@/feature/employee/types/mappers";
import { usePersonLookup } from "@/feature/person/hooks/usePersonLookup";
import { useDebouceValue } from "@/hooks/use-debounce";
import { useEffect, useState } from "react";
import { FieldPathByValue, useFormContext, useWatch } from "react-hook-form";
import { useEmployeeContext } from "./useEmployeeContext";



export function useCpfField(
    fieldName:FieldPathByValue<EmployeePayload, string | undefined>,
    dispatch:boolean = true
) {
    console.log('m::',dispatch)
    const {control,setValue} = useFormContext<EmployeePayload>()
    const {setContracts} = useEmployeeContext()
    const cpf:string | undefined = useWatch({name:fieldName,control})

    const { debounce:cpfDebounced } = useDebouceValue<string | undefined>({
        value:cpf,
        time:1000
    })

    const {lookup,isLookingUp} = usePersonLookup()

    const [hasOne,setHasOne] = useState(false)

    

    useEffect(()=>{
        if(!dispatch) return
        if(!cpfDebounced || cpfDebounced.length < 14) {
            setHasOne(false)
            return
        }


        let cancelled = false

        lookup(cpfDebounced).then((person) => {
            if(cancelled) return

            setHasOne(!!person)

            setContracts(person?.employments ?? [])

            setValue('personId', person?.id, { shouldDirty:true })
            setValue('kind', undefined, { shouldDirty:true })
            setValue('workloadId',undefined,{ shouldDirty:true})
            setValue('person',{
                cpf: cpfDebounced,
                name: person?.name ?? '',
                email: person?.email ?? '',
                birthdate: parseBirthdate(person?.birthdate),
                pixKey: person?.pixKey ?? '',
                cellphone: formatCellphone(person?.cellphone)
            },
            {
                shouldDirty:true,
            })
        })

        return () => {
            cancelled = true
        }
    },[cpfDebounced])

    return {
        isLookingUp,
        hasOne,
        hasCpf:(cpf?.length ?? 0) >= 14
    }
}
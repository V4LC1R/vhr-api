import { EmployeePayload } from "@/feature/employee/types";
import { usePersonLookup } from "@/feature/person/hooks/usePersonLookup";
import { useDebouceValue } from "@/hooks/use-debounce";
import { format } from "@react-input/mask";
import { isValid, parse } from "date-fns";
import { useEffect, useState } from "react";
import { FieldPathByValue, useFormContext, useWatch } from "react-hook-form";
import { useEmployeeContext } from "./useEmployeeContext";

const CELLPHONE_MASK = { mask: "(##) # ####-####", replacement: { '#': /\d/ } };


function formatCellphone(value: string | undefined): string {
    if (!value) return ''

    return format(value, CELLPHONE_MASK)
}


function parseBirthdate(value: string | null | undefined): Date | undefined {
    if (!value) return undefined

    const date = parse(value.slice(0, 10), 'yyyy-MM-dd', new Date())

    return isValid(date) ? date : undefined
}

export function useCpfField(fieldName:FieldPathByValue<EmployeePayload, string | undefined>) {
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
                shouldValidate:true
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
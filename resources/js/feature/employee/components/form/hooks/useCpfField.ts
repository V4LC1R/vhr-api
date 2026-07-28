import { useWatch } from "react-hook-form";

export function useCpfField(fieldName:string) {
    const cpf = useWatch({name:fieldName})

    
}
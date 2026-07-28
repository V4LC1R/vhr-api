import { useEffect, useState } from "react"

type DebouceValueProps<T> = {
    value:T
    time?:number
}

export function useDebouceValue<T>({value,time=500}:DebouceValueProps<T>){

    const [debounceValue,setDebounceValue] = useState<T>()

    useEffect(()=>{
        const timer = setTimeout(() => {
            setDebounceValue(value)
        }, time);

        return ()=>{
            clearTimeout(timer)
        }
    },[
        value,time
    ])

    return {
        debounce:debounceValue
    }
}
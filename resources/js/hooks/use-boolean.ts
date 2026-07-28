import { useCallback, useState } from "react";

export function useBoolean(defaultState:boolean = false) {
    const [val,setVal] = useState<boolean>(defaultState)

    const toggle = useCallback(()=>{
        setVal(v=>!v)
    },[])


    const setTrue = useCallback(()=>{
        setVal(()=>true)
    },[])

    const setFalse = useCallback(()=>{
        setVal(()=>false)
    },[])

    {
        return {
            value:val,
            isTrue:!!val,
            isFalse:!val,

            //
            toggle,
            setTrue,
            setFalse
        }
    }
}
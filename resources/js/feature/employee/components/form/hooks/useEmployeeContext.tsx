import { Employment } from '@/feature/employee/types';
import { createContext, ReactNode, useContext, useMemo, useState } from 'react';

type EmployeeContextProps = {
    contracts:Employment[],
    setContracts:(employments:Employment[])=>void
}

const EmployeeContext = createContext<EmployeeContextProps | null>(null)

type EmployeeContextProviderProps = {
    children:ReactNode
}
export function EmployeeContextProvider({children}:EmployeeContextProviderProps) {

    const [contracts,setContracts] = useState<Employment[]>([])

    const values:EmployeeContextProps = useMemo(()=>{
        return {
            contracts,
            setContracts
        }
    },[contracts])

    return (
        <EmployeeContext.Provider value={values}>
            {children}
        </EmployeeContext.Provider>
    )
}

export function useEmployeeContext(){
    const context = useContext(EmployeeContext)

    if(!context) {
        throw new Error('useEmployeeContext deve ser usado dentro de EmployeeContextProvider')
    }

    return context
}

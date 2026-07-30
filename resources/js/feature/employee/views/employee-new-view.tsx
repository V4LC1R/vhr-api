
import { EmployeeFormFields } from "../components/form"
import { EmployeeContextProvider } from "../components/form/hooks/useEmployeeContext";



export function EmployeeNewView() {
    
    return (
        <div className="flex flex-col gap-4 pb-6">
            <EmployeeContextProvider>
                <EmployeeFormFields/>
            </EmployeeContextProvider>
        </div>
    )
}

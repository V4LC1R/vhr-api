import { EmployeeFormFields } from "../components/form";
import { EmployeeContextProvider } from "../components/form/hooks/useEmployeeContext";
import { useEmployee } from "../hooks/useEmployee";

type Props ={
    id: string
}

export function EmployeeEditView({ id }: Props) {
    const {employee} = useEmployee(id)

    return (
        <div className="flex flex-col gap-4 pb-6">
            <EmployeeContextProvider>
                <EmployeeFormFields current={employee}/>
            </EmployeeContextProvider>
        </div>
    )
}

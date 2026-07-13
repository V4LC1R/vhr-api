import { Employee } from "@/types/employee/types"
import { EmploymentType } from "@/types/employment/types"
import {
    Combobox,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList,
} from "@/components/ui/combobox"
import { useEmployeeSearch } from "./useEmployeeSearch"

interface EmployeeSelectProps {
    value: Employee | null
    /** Entrega o colaborador completo (com activeEmployment.workload); null ao limpar. */
    onChange: (employee: Employee | null) => void
    /** Restringe aos vínculos informados (ex.: só CLT, ou só temporários). */
    kinds?: EmploymentType[]
}

function employeeLabel(employee: Employee): string {
    return employee.person?.name ?? `Nº ${employee.registerNumber}`
}

export function EmployeeSelect({ value, onChange, kinds }: EmployeeSelectProps) {
    const { employees, isLoadingEmployees, setQuery } = useEmployeeSearch({ kinds })

    return (
        <Combobox
            items={employees}
            value={value}
            onValueChange={onChange}
            // filter interno desligado — a lista já vem filtrada do servidor
            filter={null}
            itemToStringLabel={employeeLabel}
            isItemEqualToValue={(a, b) => a?.id === b?.id}
            onInputValueChange={(input) => {
                setQuery(input)
                // Esvaziar o texto limpa a seleção — senão o combobox re-preenche
                // o input com o label do colaborador ainda selecionado.
                if (!input && value) onChange(null)
            }}
        >
            <ComboboxInput
                className="w-full sm:w-72"
                showClear
                placeholder={
                    isLoadingEmployees ? "Carregando colaboradores..." : "Buscar colaborador..."
                }
            />
            <ComboboxContent>
                <ComboboxEmpty>
                    {isLoadingEmployees ? "Buscando..." : "Nenhum colaborador encontrado."}
                </ComboboxEmpty>
                <ComboboxList>
                    {(employee: Employee) => (
                        <ComboboxItem key={employee.id} value={employee}>
                            {employeeLabel(employee)}
                        </ComboboxItem>
                    )}
                </ComboboxList>
            </ComboboxContent>
        </Combobox>
    )
}

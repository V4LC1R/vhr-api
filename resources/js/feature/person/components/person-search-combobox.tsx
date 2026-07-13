import { Person } from "../types/types"
import {
    Combobox,
    ComboboxContent,
    ComboboxEmpty,
    ComboboxInput,
    ComboboxItem,
    ComboboxList,
} from "@/components/ui/combobox"
import { usePersonSearch } from "../hooks/usePersonSearch"

interface PersonSearchComboboxProps {
    /** Pessoa escolhida na busca — o consumidor decide o que preencher. */
    onSelect: (person: Person) => void
    placeholder?: string
    className?: string
}

function formatCpf(cpf: string | null): string | null {
    if (!cpf) return null

    return cpf
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d{1,2})$/, "$1-$2")
}

/** Autocomplete de pessoas por nome — busca server-side com debounce. */
export function PersonSearchCombobox({
    onSelect,
    placeholder = "Buscar por nome...",
    className,
}: PersonSearchComboboxProps) {
    const { persons, isSearchingPersons, setQuery } = usePersonSearch()

    return (
        <Combobox
            items={persons}
            // a lista já vem filtrada do servidor
            filter={null}
            itemToStringLabel={(person: Person) => person.name}
            isItemEqualToValue={(a: Person, b: Person) => a?.id === b?.id}
            onInputValueChange={setQuery}
            onValueChange={(person: Person | null) => person && onSelect(person)}
        >
            <ComboboxInput className={className} placeholder={placeholder} />
            <ComboboxContent>
                <ComboboxEmpty>
                    {isSearchingPersons ? "Buscando..." : "Ninguém encontrado com esse nome."}
                </ComboboxEmpty>
                <ComboboxList>
                    {(person: Person) => (
                        <ComboboxItem key={person.id} value={person}>
                            <div className="flex min-w-0 flex-col">
                                <span className="truncate">{person.name}</span>
                                {person.cpf && (
                                    <span className="text-xs text-muted-foreground tabular-nums">
                                        {formatCpf(person.cpf)}
                                    </span>
                                )}
                            </div>
                        </ComboboxItem>
                    )}
                </ComboboxList>
            </ComboboxContent>
        </Combobox>
    )
}

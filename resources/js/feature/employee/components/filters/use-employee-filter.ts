import { useEffect, useMemo, useState } from "react"
import { useDebouceValue } from "@/hooks/use-debounce"
import { EmployeeListFilters } from "../../types"

export type DrawerFilterKey = "status" | "kind" | "registerAt"

export const FILTER_LABELS: Record<DrawerFilterKey, string> = {
    status: "Status",
    kind: "Tipo",
    registerAt: "Data de registro",
}

const FILTER_KEYS = Object.keys(FILTER_LABELS) as DrawerFilterKey[]

type Props = {
    value: EmployeeListFilters
    onChange: (filters: EmployeeListFilters) => void
}

export function useEmployeeFilter({ value, onChange }: Props) {
    const [open, setOpen] = useState(false)
    const [draft, setDraft] = useState<EmployeeListFilters>(value)
    const [name, setName] = useState(value.name ?? "")

    const { debounce: debouncedName } = useDebouceValue({ value: name, time: 1000 })

    useEffect(() => {
        if (debouncedName === undefined) return

        const trimmed = debouncedName.trim()
        if ((value.name ?? "") === trimmed) return

        onChange({ ...value, name: trimmed || undefined })
    }, [debouncedName])

    const activeEntries = useMemo(
        () =>
            FILTER_KEYS
                .filter((key) => !!value[key])
                .map((key) => [key, value[key] as string] as const),
        [value]
    )

    function handleOpenChange(nextOpen: boolean) {
        if (nextOpen) setDraft(value)
        setOpen(nextOpen)
    }

    function handleApply() {
        onChange({ ...value, ...draft })
        setOpen(false)
    }

    function removeFilter(key: DrawerFilterKey) {
        onChange({ ...value, [key]: undefined })
    }

    function clearFilters() {
        onChange({ name: value.name })
    }

    return {
        open,
        handleOpenChange,
        draft,
        setDraft,
        handleApply,
        name,
        setName,
        activeEntries,
        filterInUse: activeEntries.length,
        removeFilter,
        clearFilters,
    }
}

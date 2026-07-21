import { format } from "date-fns"
import { FilterIcon, XIcon } from "lucide-react"

import { useIsMobile } from "@/hooks/use-mobile"
import { EMPLOYMENT_STATUSES, EMPLOYMENT_TYPES } from "@/types/employment/types"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { DatePicker } from "@/components/ui/date-picker"
import { Field, FieldLabel } from "@/components/ui/field"
import {
    Drawer,
    DrawerClose,
    DrawerContent,
    DrawerDescription,
    DrawerFooter,
    DrawerHeader,
    DrawerTitle,
    DrawerTrigger,
} from "@/components/ui/drawer"
import { EmployeeListFilters } from "../../types/types"
import { EMPLOYMENT_STATUS_LABELS, EMPLOYMENT_TYPE_LABELS } from "../table/columns"
import { cn } from "@/lib/utils"
import { Icon } from "@iconify/react"
import { Link } from "@inertiajs/react"
import { useEffect, useMemo, useState } from "react"

interface EmployeeFiltersProps {
    value: EmployeeListFilters
    onChange: (filters: EmployeeListFilters) => void
}

type DrawerFilterKey = "status" | "kind" | "registerAt"

const FILTER_LABELS: Record<DrawerFilterKey, string> = {
    status: "Status",
    kind: "Tipo",
    registerAt: "Data de registro",
}

function formatFilterValue(key: DrawerFilterKey, value: string) {
    if (key === "status") return EMPLOYMENT_STATUS_LABELS[value as keyof typeof EMPLOYMENT_STATUS_LABELS] ?? value
    if (key === "kind") return EMPLOYMENT_TYPE_LABELS[value as keyof typeof EMPLOYMENT_TYPE_LABELS] ?? value
    return value
}

export function EmployeeFilters({ value, onChange }: EmployeeFiltersProps) {
    const isMobile = useIsMobile()
    const [open, setOpen] = useState(false)
    const [draft, setDraft] = useState<EmployeeListFilters>(value)
    const [name, setName] = useState(value.name ?? "")

    const activeEntries = (["status", "kind", "registerAt"] as DrawerFilterKey[])
        .filter((key) => !!value[key])
        .map((key) => [key, value[key]] as const)

    useEffect(() => {
        const trimmed = name.trim()
        if ((value.name ?? "") === trimmed) return

        const timeout = setTimeout(() => {
            onChange({ ...value, name: trimmed || undefined })
        }, 400)

        return () => clearTimeout(timeout)
       
    }, [name])

    function handleOpenChange(nextOpen: boolean) {
        if (nextOpen) setDraft(value)
        setOpen(nextOpen)
    }

    function handleApply() {
        onChange({ ...draft, name: value.name })
        setOpen(false)
    }

    function removeFilter(key: DrawerFilterKey) {
        onChange({ ...value, [key]: undefined })
    }

    const filterInUse = useMemo(()=>{
        let filters = 0

        activeEntries.forEach(element => {
            filters++
        });

        return filters
    },[activeEntries])

    return (
        <div className="flex flex-wrap items-center justify-between gap-2">
            <div className="flex flex-wrap items-center justify-end gap-2">
                <Drawer open={open} onOpenChange={handleOpenChange} swipeDirection={isMobile ? "down" : "right"}>
                    <DrawerTrigger render={<Button variant="outline" className="px-2.5 py-1 h-8" size="sm" />}>
                        <FilterIcon />
                        {activeEntries.length > 0 ? "Editar filtros" : "Filtrar"}
                        {
                            filterInUse > 0 &&
                            <span className="rounded-xl bg-accent text-background px-1">{filterInUse}</span>
                        }
                    </DrawerTrigger>
                    <DrawerContent>
                        <DrawerHeader>
                            <DrawerTitle>Filtrar colaboradores</DrawerTitle>
                            <DrawerDescription>
                                Ajuste os filtros e aplique pra atualizar a listagem.
                            </DrawerDescription>
                        </DrawerHeader>

                        <div className="flex min-h-0 flex-1 flex-col gap-4 overflow-y-auto p-4">
                            <Field>
                                <FieldLabel>Status</FieldLabel>
                                <div className="flex flex-row flex-wrap gap-2">
                                    {EMPLOYMENT_STATUSES.map((status) => (
                                            <Button 
                                                size="default" 
                                                variant="outline"
                                                className={cn(draft.status === status ? 'bg-accent/60 text-amber-950 hover:bg-amber-300 border-amber-950 dark:text-background dark:bg-accent/80 hover:dark:bg-accent/50' :'')}
                                                onClick={()=>setDraft({ ...draft, status: draft.status === status ? undefined : status })}
                                            >
                                                {EMPLOYMENT_STATUS_LABELS[status]}
                                            </Button>
                                        ))}
                                </div>
                               
                            </Field>

                            <Field>
                                <FieldLabel>Tipo</FieldLabel>
                                <div className="flex flex-row flex-wrap gap-1">
                                    {EMPLOYMENT_TYPES.map((kind) => (
                                            <Button 
                                                size="default" 
                                                variant="outline"
                                                className={cn(draft.kind === kind ?'bg-accent/60 text-amber-950 hover:bg-amber-300 border-amber-950 dark:text-background dark:bg-accent/80 hover:dark:bg-accent/50' :'')}
                                                onClick={()=>setDraft({ ...draft, kind: draft.kind === kind ? undefined :kind})}
                                            >
                                                {EMPLOYMENT_TYPE_LABELS[kind]}
                                            </Button>
                                        ))}
                                </div>
                            </Field>

                            <Field>
                                <FieldLabel htmlFor="filter-register-at">Data de registro</FieldLabel>
                                <DatePicker
                                    id="filter-register-at"
                                    value={draft.registerAt ? new Date(`${draft.registerAt}T00:00:00`) : null}
                                    onChange={(date) =>
                                        setDraft({
                                            ...draft,
                                            registerAt: date ? format(date, "yyyy-MM-dd") : undefined,
                                        })
                                    }
                                />
                            </Field>
                        </div>

                        <DrawerFooter>
                            <Button onClick={handleApply}>Aplicar filtros</Button>
                            <DrawerClose render={<Button variant="outline" />}>Cancelar</DrawerClose>
                        </DrawerFooter>
                    </DrawerContent>
                </Drawer>
                {activeEntries.length > 0 && (
                    <Button variant="ghost" size="sm" onClick={() => onChange({ name: value.name })}>
                        Limpar filtros
                    </Button>
                )}
            </div>
            <Button 
                className="h-8 rounded-md"
                render={<Link href="/dashboard/employees/create"/>}
            >
                <Icon  icon="fluent:add-12-filled"/>Adicionar
            </Button>
        </div>
    )
}

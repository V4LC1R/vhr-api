import * as React from "react"
import { FilterIcon, XIcon } from "lucide-react"

import { useIsMobile } from "@/hooks/use-mobile"
import { EMPLOYMENT_STATUSES, EMPLOYMENT_TYPES } from "@/types/employment/types"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Field, FieldLabel } from "@/components/ui/field"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
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

interface EmployeeFiltersProps {
    value: EmployeeListFilters
    onChange: (filters: EmployeeListFilters) => void
}

const FILTER_LABELS: Record<keyof EmployeeListFilters, string> = {
    name: "Nome",
    status: "Status",
    kind: "Tipo",
    registerAt: "Data de registro",
}

function formatFilterValue(key: keyof EmployeeListFilters, value: string) {
    if (key === "status") return EMPLOYMENT_STATUS_LABELS[value as keyof typeof EMPLOYMENT_STATUS_LABELS] ?? value
    if (key === "kind") return EMPLOYMENT_TYPE_LABELS[value as keyof typeof EMPLOYMENT_TYPE_LABELS] ?? value
    return value
}

export function EmployeeFilters({ value, onChange }: EmployeeFiltersProps) {
    const isMobile = useIsMobile()
    const [open, setOpen] = React.useState(false)
    const [draft, setDraft] = React.useState<EmployeeListFilters>(value)

    const activeEntries = (Object.entries(value) as [keyof EmployeeListFilters, string | undefined][]).filter(
        ([, v]) => !!v
    )

    function handleOpenChange(nextOpen: boolean) {
        if (nextOpen) setDraft(value)
        setOpen(nextOpen)
    }

    function handleApply() {
        onChange(draft)
        setOpen(false)
    }

    function removeFilter(key: keyof EmployeeListFilters) {
        onChange({ ...value, [key]: undefined })
    }

    return (
        <div className="flex flex-wrap items-center justify-end gap-2">
            {activeEntries.map(([key, v]) => (
                <Badge key={key} variant="secondary" className="gap-1 pr-1">
                    {FILTER_LABELS[key]}: {formatFilterValue(key, v!)}
                    <button
                        type="button"
                        onClick={() => removeFilter(key)}
                        className="rounded-full p-0.5 hover:bg-foreground/10"
                        aria-label={`Remover filtro ${FILTER_LABELS[key]}`}
                    >
                        <XIcon className="size-3" />
                    </button>
                </Badge>
            ))}

            {activeEntries.length > 0 && (
                <Button variant="ghost" size="sm" onClick={() => onChange({})}>
                    Limpar filtros
                </Button>
            )}

            <Drawer open={open} onOpenChange={handleOpenChange} swipeDirection={isMobile ? "down" : "right"}>
                <DrawerTrigger render={<Button variant="outline" size="sm" />}>
                    <FilterIcon />
                    {activeEntries.length > 0 ? "Editar filtros" : "Filtrar"}
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
                            <FieldLabel htmlFor="filter-name">Nome</FieldLabel>
                            <Input
                                id="filter-name"
                                placeholder="Buscar por nome"
                                value={draft.name ?? ""}
                                onChange={(e) =>
                                    setDraft({ ...draft, name: e.target.value || undefined })
                                }
                            />
                        </Field>

                        <Field>
                            <FieldLabel>Status</FieldLabel>
                            <Select
                                items={EMPLOYMENT_STATUS_LABELS}
                                value={draft.status ?? null}
                                onValueChange={(v) =>
                                    setDraft({ ...draft, status: v ?? undefined })
                                }
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={null}>Todos</SelectItem>
                                    {EMPLOYMENT_STATUSES.map((status) => (
                                        <SelectItem key={status} value={status}>
                                            {EMPLOYMENT_STATUS_LABELS[status]}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field>
                            <FieldLabel>Tipo</FieldLabel>
                            <Select
                                items={EMPLOYMENT_TYPE_LABELS}
                                value={draft.kind ?? null}
                                onValueChange={(v) =>
                                    setDraft({ ...draft, kind: v ?? undefined })
                                }
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Todos" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={null}>Todos</SelectItem>
                                    {EMPLOYMENT_TYPES.map((kind) => (
                                        <SelectItem key={kind} value={kind}>
                                            {EMPLOYMENT_TYPE_LABELS[kind]}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field>
                            <FieldLabel htmlFor="filter-register-at">Data de registro</FieldLabel>
                            <Input
                                id="filter-register-at"
                                type="date"
                                value={draft.registerAt ?? ""}
                                onChange={(e) =>
                                    setDraft({ ...draft, registerAt: e.target.value || undefined })
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
        </div>
    )
}

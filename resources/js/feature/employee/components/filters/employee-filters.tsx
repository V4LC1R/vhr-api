import * as React from "react"
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
    const [open, setOpen] = React.useState(false)
    const [draft, setDraft] = React.useState<EmployeeListFilters>(value)
    const [name, setName] = React.useState(value.name ?? "")

    const activeEntries = (["status", "kind", "registerAt"] as DrawerFilterKey[])
        .filter((key) => !!value[key])
        .map((key) => [key, value[key]] as const)

    React.useEffect(() => {
        const trimmed = name.trim()
        if ((value.name ?? "") === trimmed) return

        const timeout = setTimeout(() => {
            onChange({ ...value, name: trimmed || undefined })
        }, 400)

        return () => clearTimeout(timeout)
        // eslint-disable-next-line react-hooks/exhaustive-deps
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

    return (
        <div className="flex flex-wrap items-center justify-between gap-2">
            <Input
                placeholder="Buscar por nome"
                value={name}
                onChange={(e) => setName(e.target.value)}
                className="max-w-64"
            />

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
                    <Button variant="ghost" size="sm" onClick={() => onChange({ name: value.name })}>
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
            </div>
        </div>
    )
}

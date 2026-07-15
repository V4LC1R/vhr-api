import * as React from "react"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import type { DateRange } from "react-day-picker"
import { CalendarIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"

interface DateRangePickerProps {
    value: DateRange | undefined
    onChange: (range: DateRange | undefined) => void
    placeholder?: string
    disabled?: boolean
    id?: string
    className?: string
}

export function DateRangePicker({
    value,
    onChange,
    placeholder = "Selecione um período",
    disabled,
    id,
    className,
}: DateRangePickerProps) {
    const [open, setOpen] = React.useState(false)

    function label() {
        if (!value?.from) return placeholder
        if (!value.to) return format(value.from, "dd/MM/yyyy", { locale: ptBR })

        return `${format(value.from, "dd/MM/yyyy", { locale: ptBR })} – ${format(value.to, "dd/MM/yyyy", { locale: ptBR })}`
    }

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger
                render={
                    <Button
                        id={id}
                        variant="outline"
                        disabled={disabled}
                        className={cn(
                            "h-10 w-full justify-start gap-2 font-normal",
                            !value?.from && "text-muted-foreground",
                            className
                        )}
                    />
                }
            >
                <CalendarIcon className="size-4 shrink-0" />
                <span className="truncate">{label()}</span>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0">
                <Calendar
                    mode="range"
                    numberOfMonths={2}
                    selected={value}
                    onSelect={(range) => {
                        onChange(range)
                        if (range?.from && range?.to) setOpen(false)
                    }}
                    locale={ptBR}
                />
            </PopoverContent>
        </Popover>
    )
}

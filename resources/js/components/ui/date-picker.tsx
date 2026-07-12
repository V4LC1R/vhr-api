import * as React from "react"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { CalendarIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"

interface DatePickerProps {
    value: Date | null
    onChange: (date: Date | null) => void
    placeholder?: string
    disabled?: boolean
    id?: string
    className?: string
}

export function DatePicker({
    value,
    onChange,
    placeholder = "Selecione uma data",
    disabled,
    id,
    className,
}: DatePickerProps) {
    const [open, setOpen] = React.useState(false)

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
                            !value && "text-muted-foreground",
                            className
                        )}
                    />
                }
            >
                <CalendarIcon className="size-4 shrink-0" />
                <span className="truncate">{value ? format(value, "dd/MM/yyyy", { locale: ptBR }) : placeholder}</span>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0">
                <Calendar
                    mode="single"
                    selected={value ?? undefined}
                    onSelect={(date) => {
                        onChange(date ?? null)
                        setOpen(false)
                    }}
                    locale={ptBR}
                />
            </PopoverContent>
        </Popover>
    )
}

import { addMonths, format, subMonths } from "date-fns"
import { ptBR } from "date-fns/locale"
import { ChevronLeftIcon, ChevronRightIcon } from "lucide-react"

import { Button } from "@/components/ui/button"

interface MonthPickerProps {
    value: Date
    onChange: (value: Date) => void
    disabled?: boolean
}

export function MonthPicker({ value, onChange, disabled }: MonthPickerProps) {
    const label = format(value, "MMMM yyyy", { locale: ptBR })

    return (
        <div className="flex items-center gap-1">
            <Button
                variant="outline"
                size="icon-sm"
                disabled={disabled}
                onClick={() => onChange(subMonths(value, 1))}
                aria-label="Mês anterior"
            >
                <ChevronLeftIcon />
            </Button>
            <span className="min-w-36 text-center text-sm font-medium capitalize">
                {label}
            </span>
            <Button
                variant="outline"
                size="icon-sm"
                disabled={disabled}
                onClick={() => onChange(addMonths(value, 1))}
                aria-label="Próximo mês"
            >
                <ChevronRightIcon />
            </Button>
        </div>
    )
}

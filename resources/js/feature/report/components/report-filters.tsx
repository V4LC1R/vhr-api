import type { DateRange } from "react-day-picker"

import { Input } from "@/components/ui/input"
import { DateRangePicker } from "@/components/ui/date-range-picker"

interface ReportFiltersBarProps {
    range: DateRange | undefined
    onRangeChange: (range: DateRange | undefined) => void
    name: string
    onNameChange: (name: string) => void
}

export function ReportFiltersBar({ range, onRangeChange, name, onNameChange }: ReportFiltersBarProps) {
    return (
        <div className="flex flex-wrap items-center gap-2">
            <DateRangePicker value={range} onChange={onRangeChange} className="sm:w-72" />
            <Input
                value={name}
                onChange={(e) => onNameChange(e.target.value)}
                placeholder="Buscar por nome..."
                className="sm:w-64"
            />
        </div>
    )
}

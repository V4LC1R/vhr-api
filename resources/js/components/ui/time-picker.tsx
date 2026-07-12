import * as React from "react"
import { ClockIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { Button } from "@/components/ui/button"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"

interface TimePickerProps {
    value: string | null
    onChange: (time: string | null) => void
    stepMinutes?: number
    placeholder?: string
    disabled?: boolean
    id?: string
    className?: string
}

function buildTimeOptions(stepMinutes: number) {
    const options: string[] = []
    for (let minutes = 0; minutes < 24 * 60; minutes += stepMinutes) {
        const hours = Math.floor(minutes / 60)
        const mins = minutes % 60
        options.push(`${String(hours).padStart(2, "0")}:${String(mins).padStart(2, "0")}`)
    }
    return options
}

function closestTimeOption(options: string[], time: string) {
    const [h, m] = time.split(":").map(Number)
    const targetMinutes = h * 60 + m
    return options.reduce((closest, option) => {
        const [oh, om] = option.split(":").map(Number)
        const optionMinutes = oh * 60 + om
        const [ch, cm] = closest.split(":").map(Number)
        const closestMinutes = ch * 60 + cm
        return Math.abs(optionMinutes - targetMinutes) < Math.abs(closestMinutes - targetMinutes)
            ? option
            : closest
    }, options[0])
}

export function TimePicker({
    value,
    onChange,
    stepMinutes = 30,
    placeholder = "Selecione um horário",
    disabled,
    id,
    className,
}: TimePickerProps) {
    const [open, setOpen] = React.useState(false)
    const listRef = React.useRef<HTMLDivElement>(null)
    const options = React.useMemo(() => buildTimeOptions(stepMinutes), [stepMinutes])

    React.useEffect(() => {
        if (!open) return

        const now = new Date()
        const target = value ?? closestTimeOption(
            options,
            `${String(now.getHours()).padStart(2, "0")}:${String(now.getMinutes()).padStart(2, "0")}`
        )
        const item = listRef.current?.querySelector(`[data-time="${target}"]`)
        item?.scrollIntoView({ block: "center" })
    }, [open, value, options])

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
                <ClockIcon className="size-4 shrink-0" />
                <span className="truncate">{value ?? placeholder}</span>
            </PopoverTrigger>
            <PopoverContent className="w-(--anchor-width) min-w-36 p-1">
                <div ref={listRef} className="max-h-64 overflow-y-auto">
                    {options.map((option) => {
                        const isSelected = option === value

                        return (
                            <button
                                key={option}
                                type="button"
                                data-time={option}
                                onClick={() => {
                                    onChange(option)
                                    setOpen(false)
                                }}
                                className={cn(
                                    "flex w-full items-center rounded-md px-2.5 py-1 text-sm transition-colors",
                                    isSelected
                                        ? "bg-primary text-primary-foreground dark:bg-accent dark:text-accent-foreground"
                                        : "hover:bg-green-100 hover:text-green-900 dark:hover:bg-accent dark:hover:text-accent-foreground"
                                )}
                            >
                                {option}
                            </button>
                        )
                    })}
                </div>
            </PopoverContent>
        </Popover>
    )
}

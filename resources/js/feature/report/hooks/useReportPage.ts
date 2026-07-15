import { useEffect, useState } from "react"
import { format, startOfMonth } from "date-fns"
import type { DateRange } from "react-day-picker"

import { useAttendanceReport } from "./useAttendanceReport"

function toIso(date: Date) {
    return format(date, "yyyy-MM-dd")
}

/**
 * Estado compartilhado pelas 3 telas de relatório: intervalo de datas (padrão =
 * mês corrente), busca por nome (debounced) e o fetch reativo a ambos.
 */
export function useReportPage(endpoint: string) {
    const [range, setRange] = useState<DateRange | undefined>({
        from: startOfMonth(new Date()),
        to: new Date(),
    })
    const [nameInput, setNameInput] = useState("")
    const [name, setName] = useState("")

    const { fetch, isLoading, rows } = useAttendanceReport(endpoint)

    useEffect(() => {
        const handle = setTimeout(() => setName(nameInput.trim()), 300)
        return () => clearTimeout(handle)
    }, [nameInput])

    const isoRange = range?.from && range?.to
        ? { from: toIso(range.from), to: toIso(range.to) }
        : null

    useEffect(() => {
        if (!isoRange) return
        fetch({ ...isoRange, ...(name ? { name } : {}) })
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [isoRange?.from, isoRange?.to, name])

    return {
        range,
        setRange,
        name: nameInput,
        setName: setNameInput,
        rows,
        isLoading,
        isoRange,
    }
}

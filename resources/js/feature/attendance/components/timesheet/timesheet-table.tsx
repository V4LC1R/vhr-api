import { useMemo } from "react"
import {
    eachDayOfInterval,
    endOfMonth,
    format,
    isSameMonth,
    isToday,
    isWeekend,
    startOfMonth,
} from "date-fns"

import { cn } from "@/lib/utils"
import { useIsMobile } from "@/hooks/use-mobile"
import { DailyEngagement } from "@/types/dailyEngagement/types"
import { EmploymentType } from "@/types/employment/types"
import { DataTable } from "@/components/table/data-table"
import {
    getTimesheetColumns,
    TimesheetColumnActions,
    TimesheetRow,
    TimesheetTotals,
} from "./columns"

interface TimesheetTableProps extends TimesheetColumnActions {
    registerDate: Date
    monthDate: Date
    daysByDate: Map<string, DailyEngagement>
    isLoading?: boolean
    /** Kind do vínculo ativo — não-CLT ganha "Presente" e dayli a coluna Diária. */
    employmentKind?: EmploymentType
}

export function TimesheetTable({
    registerDate,
    monthDate,
    daysByDate,
    isLoading,
    employmentKind,
    ...actions
}: TimesheetTableProps) {
    const isMobile = useIsMobile()

    // Uma linha por dia — no mês da admissão o intervalo começa no registerDate.
    const rows = useMemo<TimesheetRow[]>(() => {
        const startDate = isSameMonth(monthDate, registerDate)
            ? registerDate
            : startOfMonth(monthDate)

        return eachDayOfInterval({ start: startDate, end: endOfMonth(monthDate) }).map(
            (dayDate) => {
                const date = format(dayDate, "yyyy-MM-dd")
                return { date, dayDate, day: daysByDate.get(date) }
            }
        )
    }, [monthDate, registerDate, daysByDate])

    const totals = useMemo<TimesheetTotals>(() => {
        const engagements = [...daysByDate.values()]

        return {
            worked: engagements.reduce((sum, day) => sum + day.workedMinutes, 0),
            expected: engagements.reduce((sum, day) => sum + day.expectedMinutes, 0),
            balance: engagements.reduce((sum, day) => sum + day.balanceMinutes, 0),
            diarias: engagements.reduce((sum, day) => sum + (day.diariaValue ?? 0), 0),
        }
    }, [daysByDate])

    // Sem memo de propósito: os handlers mudam a cada render e memoizar
    // parcialmente deixaria closures velhas nas células (máx. 31 linhas).
    const columns = getTimesheetColumns(isMobile, totals, actions, {
        showDiaria: employmentKind === "dayli",
        presenceMode: !!employmentKind && employmentKind !== "clt",
    })

    return (
        <DataTable
            data={rows}
            columns={columns}
            isLoading={isLoading}
            rowClassName={(row) =>
                cn(
                    isWeekend(row.original.dayDate) && "bg-muted/40",
                    isToday(row.original.dayDate) && "bg-primary/5"
                )
            }
        />
    )
}

import { useState } from "react"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import toast from "react-hot-toast"

import { extractErrorMessage } from "@/lib/http"
import { DailyEngagement } from "@/types/dailyEngagement/types"
import { useUpsertDayException } from "../../hooks/useUpsertDayException"
import { ExceptionPayload } from "../../types/schemas"

type ExceptionDialogState = {
    open: boolean
    date: string
    day: DailyEngagement | null
}

type UseExceptionDialogParams = {
    employeeId: string | null
    /** Chamado após salvar — a tela usa pra recarregar os dias do mês. */
    onSaved: () => Promise<void> | void
}

/** Estado + submit do ExceptionDialog; o retorno liga direto nos props do componente. */
export function useExceptionDialog({ employeeId, onSaved }: UseExceptionDialogParams) {
    const [state, setState] = useState<ExceptionDialogState>({
        open: false,
        date: "",
        day: null,
    })
    const { upsert: upsertException, isUpsertingException } = useUpsertDayException()

    function openFor(date: string, day?: DailyEngagement | null) {
        setState({ open: true, date, day: day ?? null })
    }

    function setOpen(open: boolean) {
        setState((current) => ({ ...current, open }))
    }

    async function submit(values: ExceptionPayload) {
        if (!employeeId) return

        try {
            await upsertException({
                employeeId,
                date: state.date,
                type: values.type,
                // campo vazio limpa a observação no back (null ≠ ausente, que preservaria)
                note: values.note?.trim() ? values.note.trim() : null,
            })
            toast.success("Tipo do dia atualizado!")
            setOpen(false)
            await onSaved()
        } catch (error) {
            toast.error(extractErrorMessage(error, "Não foi possível atualizar o dia."))
        }
    }

    return {
        open: state.open,
        setOpen,
        openFor,
        description: state.date
            ? format(new Date(`${state.date}T00:00:00`), "EEEE, dd 'de' MMMM", { locale: ptBR })
            : undefined,
        defaultValues: state.day
            ? { type: state.day.type, note: state.day.note ?? "" }
            : undefined,
        isSubmitting: isUpsertingException,
        submit,
    }
}

import { CheckIcon, XIcon } from "lucide-react"

import { Button } from "@/components/ui/button"

interface SelectionBarProps {
    count: number
    isBusy?: boolean
    onClear: () => void
    onRejectSelected: () => void
    onApproveSelected: () => void
}

/** Barra de ações em lote — aparece quando há dias marcados na fila. */
export function SelectionBar({
    count,
    isBusy,
    onClear,
    onRejectSelected,
    onApproveSelected,
}: SelectionBarProps) {
    if (count === 0) return null

    return (
        <div className="flex flex-wrap items-center justify-between gap-2 rounded-xl border bg-card px-3 py-2">
            <span className="text-sm">
                <span className="font-medium">{count}</span>{" "}
                {count === 1 ? "dia marcado" : "dias marcados"}
            </span>
            <div className="flex items-center gap-2">
                <Button variant="ghost" size="sm" onClick={onClear}>
                    Limpar
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    className="text-destructive hover:text-destructive"
                    disabled={isBusy}
                    onClick={onRejectSelected}
                >
                    <XIcon />
                    Rejeitar marcados
                </Button>
                <Button size="sm" disabled={isBusy} onClick={onApproveSelected}>
                    <CheckIcon />
                    Aceitar marcados
                </Button>
            </div>
        </div>
    )
}

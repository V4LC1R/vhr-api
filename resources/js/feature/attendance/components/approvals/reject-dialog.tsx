import { useEffect, useState } from "react"
import { XIcon } from "lucide-react"

import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Field, FieldLabel } from "@/components/ui/field"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"

interface RejectDialogProps {
    open: boolean
    count: number
    isRejecting?: boolean
    onOpenChange: (open: boolean) => void
    onConfirm: (note: string) => void
}

export function RejectDialog({
    open,
    count,
    isRejecting,
    onOpenChange,
    onConfirm,
}: RejectDialogProps) {
    const [note, setNote] = useState("")

    useEffect(() => {
        if (open) setNote("")
    }, [open])

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>Rejeitar {count > 1 ? `${count} dias` : "dia"}</DialogTitle>
                    <DialogDescription>
                        Os dias rejeitados voltam pra correção e reenvio.
                    </DialogDescription>
                </DialogHeader>

                <Field>
                    <FieldLabel htmlFor="reject-note">Motivo</FieldLabel>
                    <Input
                        id="reject-note"
                        value={note}
                        onChange={(e) => setNote(e.target.value)}
                        placeholder="Explique o que precisa ser corrigido"
                        maxLength={255}
                    />
                </Field>

                <DialogFooter>
                    <DialogClose render={<Button variant="outline" />}>Cancelar</DialogClose>
                    <Button
                        variant="destructive"
                        disabled={isRejecting || !note.trim()}
                        onClick={() => onConfirm(note.trim())}
                    >
                        <XIcon />
                        {isRejecting ? "Rejeitando..." : "Rejeitar"}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

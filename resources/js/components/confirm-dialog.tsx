import { Loader2Icon, type LucideIcon } from "lucide-react"

import { Button } from "@/components/ui/button"
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog"

interface ConfirmDialogProps {
    open: boolean
    onOpenChange: (open: boolean) => void
    title: string
    description?: string
    confirmLabel?: string
    cancelLabel?: string
    confirmIcon?: LucideIcon
    destructive?: boolean
    isLoading?: boolean
    onConfirm: () => void
}

export function ConfirmDialog({
    open,
    onOpenChange,
    title,
    description,
    confirmLabel = "Confirmar",
    cancelLabel = "Cancelar",
    confirmIcon: ConfirmIcon,
    destructive,
    isLoading,
    onConfirm,
}: ConfirmDialogProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    {description && <DialogDescription>{description}</DialogDescription>}
                </DialogHeader>
                <DialogFooter>
                    <DialogClose render={<Button variant="outline" />}>{cancelLabel}</DialogClose>
                    <Button
                        variant={destructive ? "destructive" : "default"}
                        disabled={isLoading}
                        onClick={onConfirm}
                    >
                        {isLoading ? (
                            <Loader2Icon className="animate-spin" />
                        ) : (
                            ConfirmIcon && <ConfirmIcon />
                        )}
                        {confirmLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    )
}

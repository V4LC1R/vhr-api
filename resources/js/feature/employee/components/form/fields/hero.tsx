import { UserPlusIcon } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { cn } from "@/lib/utils"
import { useWatch } from "react-hook-form"

export function EmployeeHero() {
    const name = useWatch({name:'name'})

    return (
        <div className="flex flex-col gap-4 rounded-xl border bg-card p-6 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-center gap-4">
                <div className="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <UserPlusIcon className="size-6" />
                </div>
                <div className="flex flex-col gap-1">
                    <span className="text-xs text-muted-foreground">Novo colaborador</span>
                    <span
                        className={cn(
                            "text-2xl font-semibold tracking-tight",
                            !name && "text-muted-foreground"
                        )}
                    >
                        {name || "Nome do colaborador"}
                    </span>
                </div>
            </div>

            <div className="flex flex-col items-start gap-1 sm:items-end">
                <span className="text-xs text-muted-foreground">Registro provável</span>
                
                    <Badge variant="secondary" className="text-sm">
                        Nº {'2'}
                    </Badge>
                
            </div>
        </div>
    )
}

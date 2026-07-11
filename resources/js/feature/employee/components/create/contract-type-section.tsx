import {
    BriefcaseIcon,
    CalendarDaysIcon,
    CheckIcon,
    HourglassIcon,
    UserRoundIcon,
    type LucideIcon,
} from "lucide-react"

import { EmploymentType } from "@/types/employment/types"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { FieldError } from "@/components/ui/field"
import { cn } from "@/lib/utils"

interface ContractTypeSectionProps {
    value: EmploymentType | null
    onChange: (kind: EmploymentType) => void
    error?: string
}

const CONTRACT_TYPES: { value: EmploymentType; label: string; description: string; icon: LucideIcon }[] = [
    {
        value: "clt",
        label: "CLT",
        description: "Vínculo formal, carteira assinada e direitos trabalhistas completos.",
        icon: BriefcaseIcon,
    },
    {
        value: "dayli",
        label: "Diarista",
        description: "Pagamento por dia trabalhado, sem vínculo fixo.",
        icon: CalendarDaysIcon,
    },
    {
        value: "temporary",
        label: "Temporário",
        description: "Contrato por prazo determinado, para demandas pontuais.",
        icon: HourglassIcon,
    },
    {
        value: "freelancer",
        label: "Freelancer",
        description: "Prestador autônomo, sem vínculo empregatício.",
        icon: UserRoundIcon,
    },
]

export function ContractTypeSection({ value, onChange, error }: ContractTypeSectionProps) {
    return (
        <Card>
            <CardHeader>
                <CardTitle>Vínculo</CardTitle>
            </CardHeader>
            <CardContent className="flex flex-col gap-3">
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    {CONTRACT_TYPES.map((type) => {
                        const Icon = type.icon
                        const isSelected = value === type.value

                        return (
                            <button
                                key={type.value}
                                type="button"
                                onClick={() => onChange(type.value)}
                                aria-pressed={isSelected}
                                className={cn(
                                    "group relative flex aspect-square flex-col items-center justify-center gap-2 overflow-hidden rounded-xl border p-4 text-center transition-all duration-300 ease-out",
                                    "hover:-translate-y-1 hover:border-primary/60 hover:shadow-lg hover:shadow-primary/20",
                                    "dark:hover:border-accent dark:hover:shadow-accent/20",
                                    "focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-primary/50 dark:focus-visible:ring-accent/50",
                                    isSelected
                                        ? "border-primary bg-primary/10 shadow-md shadow-primary/10 dark:border-accent dark:bg-accent/10 dark:shadow-accent/10"
                                        : "border-border bg-card"
                                )}
                            >
                                <span
                                    aria-hidden
                                    className={cn(
                                        "pointer-events-none absolute inset-0 bg-linear-to-b from-transparent via-transparent to-transparent opacity-0 transition-opacity duration-300 dark:from-accent/15",
                                        "dark:group-hover:opacity-100",
                                        isSelected && "dark:opacity-100"
                                    )}
                                />
                                <div
                                    className={cn(
                                        "relative z-10 flex size-10 items-center justify-center rounded-full transition-colors duration-300",
                                        isSelected
                                            ? "bg-primary text-primary-foreground dark:bg-accent dark:text-accent-foreground"
                                            : "bg-muted text-muted-foreground group-hover:bg-primary group-hover:text-primary-foreground dark:group-hover:bg-accent dark:group-hover:text-accent-foreground"
                                    )}
                                >
                                    <Icon className="size-5" />
                                    {isSelected && (
                                        <span className="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-primary text-primary-foreground ring-2 ring-card dark:bg-accent dark:text-accent-foreground">
                                            <CheckIcon className="size-2.5" />
                                        </span>
                                    )}
                                </div>
                                <span
                                    className={cn(
                                        "relative z-10 text-sm font-medium transition-colors duration-300",
                                        isSelected
                                            ? "text-primary dark:text-accent"
                                            : "text-foreground group-hover:text-primary dark:group-hover:text-accent"
                                    )}
                                >
                                    {type.label}
                                </span>
                                <span className="relative z-10 text-xs text-muted-foreground">
                                    {type.description}
                                </span>
                            </button>
                        )
                    })}
                </div>
                <FieldError errors={error ? [{ message: error }] : undefined} />
            </CardContent>
        </Card>
    )
}

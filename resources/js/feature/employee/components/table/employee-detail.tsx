import * as React from "react"
import { format } from "date-fns"
import { ptBR } from "date-fns/locale"
import { Employee } from "../../types/types"
import { cn } from "@/lib/utils"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import {
    EMPLOYMENT_STATUS_LABELS,
    EMPLOYMENT_STATUS_VARIANTS,
    EMPLOYMENT_TYPE_LABELS,
} from "./columns"

interface EmployeeDetailProps {
    employee: Employee
}

function DetailField({ label, value }: { label: string; value?: React.ReactNode }) {
    return (
        <div className="flex min-w-0 flex-col gap-0.5">
            <span className="text-xs text-muted-foreground">{label}</span>
            <span className="text-sm wrap-break-word">{value ?? "—"}</span>
        </div>
    )
}

function DetailSection({
    title,
    children,
    className,
    contentClassName,
}: {
    title: string
    children: React.ReactNode
    className?: string
    contentClassName?: string
}) {
    return (
        <Card size="sm" className={cn("flex-1", className)}>
            <CardHeader>
                <CardTitle className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    {title}
                </CardTitle>
            </CardHeader>
            <CardContent className={cn("grid grid-cols-2 gap-4", contentClassName)}>
                {children}
            </CardContent>
        </Card>
    )
}

function formatTime(time?: string) {
    return time?.slice(0, 5)
}

function formatDate(date?: string | null) {
    return date ? format(new Date(date), "dd/MM/yyyy", { locale: ptBR }) : undefined
}

export function EmployeeDetail({ employee }: EmployeeDetailProps) {
    const { person, activeEmployment } = employee
    const workload = activeEmployment?.workload

    return (
        <div className="flex flex-col gap-3 px-2 py-3">
            <div className="flex flex-col gap-3 sm:flex-row">
                <DetailSection title="Contato">
                    <DetailField label="E-mail" value={person?.email} />
                    <DetailField label="Celular" value={person?.cellphone} />
                </DetailSection>

                <DetailSection title="Vínculo">
                    <DetailField
                        label="Tipo"
                        value={
                            activeEmployment && (
                                <Badge variant="outline">
                                    {EMPLOYMENT_TYPE_LABELS[activeEmployment.kind]}
                                </Badge>
                            )
                        }
                    />
                    <DetailField
                        label="Status"
                        value={
                            activeEmployment && (
                                <Badge variant={EMPLOYMENT_STATUS_VARIANTS[activeEmployment.status]}>
                                    {EMPLOYMENT_STATUS_LABELS[activeEmployment.status]}
                                </Badge>
                            )
                        }
                    />
                    <DetailField label="Data de registro" value={formatDate(activeEmployment?.registerAt)} />
                    <DetailField label="Desligado em" value={formatDate(activeEmployment?.leftAt)} />
                </DetailSection>
            </div>

            <DetailSection title="Jornada" contentClassName="sm:grid-cols-4">
                <DetailField label="Descrição" value={workload?.description} />
                <DetailField
                    label="Carga horária"
                    value={workload && `${workload.weeklyHours}h/semana · ${workload.monthlyHours}h/mês`}
                />
                <DetailField
                    label="Horário"
                    value={workload && `${formatTime(workload.entryTime)} às ${formatTime(workload.leftTime)}`}
                />
                <DetailField
                    label="Intervalo"
                    value={
                        workload?.interval.startAt && workload.interval.endAt
                            ? `${formatTime(workload.interval.startAt)} às ${formatTime(workload.interval.endAt)}`
                            : undefined
                    }
                />
            </DetailSection>
        </div>
    )
}

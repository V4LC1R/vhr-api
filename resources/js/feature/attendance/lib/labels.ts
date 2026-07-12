import { DailyEngagementStatus, DailyEngagementType } from "@/types/dailyEngagement/types";
import { TimeEntryType } from "@/types/timeEntry/types";

export const DAY_TYPE_LABELS: Record<DailyEngagementType, string> = {
    work: "Trabalho",
    day_off: "Folga",
    holiday: "Feriado",
    medical: "Atestado",
    absence: "Falta",
};

export const DAY_TYPE_VARIANTS: Record<
    DailyEngagementType,
    "default" | "secondary" | "outline" | "destructive"
> = {
    work: "outline",
    day_off: "secondary",
    holiday: "secondary",
    medical: "secondary",
    absence: "destructive",
};

export const DAY_STATUS_LABELS: Record<DailyEngagementStatus, string> = {
    draft: "Rascunho",
    pending: "Pendente",
    approved: "Aprovado",
    rejected: "Rejeitado",
};

export const DAY_STATUS_VARIANTS: Record<
    DailyEngagementStatus,
    "default" | "secondary" | "outline" | "destructive"
> = {
    draft: "outline",
    pending: "secondary",
    approved: "default",
    rejected: "destructive",
};

export const PUNCH_TYPE_LABELS: Record<TimeEntryType, string> = {
    entry: "Entrada",
    exit: "Saída",
};

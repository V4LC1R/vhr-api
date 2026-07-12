import { DailyEngagement } from "@/types/dailyEngagement/types";

/** Dias pendentes de um colaborador, agregados pra tela de aprovação. */
export interface ApprovalGroup {
    employeeId: string;
    name: string;
    days: DailyEngagement[];
    workedMinutes: number;
    expectedMinutes: number;
    balanceMinutes: number;
    /** Algum dia tem sequência de marcações inconsistente. */
    hasAnomaly: boolean;
}

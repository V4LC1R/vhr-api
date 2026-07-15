/**
 * Linha agregada por funcionário — resposta de `GET /api/v1/reports/*`.
 * Sempre sobre dias aprovados no intervalo informado.
 */
export interface ReportRow {
    employeeId: string;
    registerNumber: number | null;
    personName: string | null;
    kind: string | null;
    workedMinutes: number;
    workedHoursDecimal: number; // workedMinutes/60 (ex.: 90min -> 1.5)
    expectedMinutes: number;
    balanceMinutes: number; // saldo líquido do período (pode ser negativo)
    negativeBalanceMinutes: number; // soma só dos dias com saldo negativo
    absenceDays: number;
    diasTrabalhados: number;
    diariaValueTotal: number | null; // null pra quem não tem diária calculada
}

export type ReportFilters = {
    from: string; // YYYY-MM-DD
    to: string; // YYYY-MM-DD
    name?: string;
};

export type ReportResponse = {
    data: ReportRow[];
};

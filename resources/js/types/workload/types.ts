/**
 * Resource (resposta da API) — WorkloadResource (camelCase).
 */
export interface WorkloadInterval {
    startAt: string | null;
    endAt: string | null;
}

export interface Workload {
    id: string;
    companyId: string;
    description: string;
    monthlyHours: number;
    weeklyHours: number;
    entryTime: string; // "HH:mm:ss"
    leftTime: string;
    interval: WorkloadInterval;
    createdAt: string;
    updatedAt: string;
}

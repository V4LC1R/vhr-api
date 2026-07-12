import type { Employee } from '@/types/employee/types';
import type { TimeEntry } from '@/types/timeEntry/types';

/**
 * Resource (resposta da API) — DailyEngagementResource.
 * Enums do módulo Attendance vivem aqui (const array = fonte única p/ tipo e schema).
 */
export const DAILY_ENGAGEMENT_STATUSES = ['draft', 'pending', 'approved', 'rejected'] as const;
export type DailyEngagementStatus = (typeof DAILY_ENGAGEMENT_STATUSES)[number];

export const DAILY_ENGAGEMENT_TYPES = ['work', 'day_off', 'holiday', 'medical', 'absence'] as const;
export type DailyEngagementType = (typeof DAILY_ENGAGEMENT_TYPES)[number];

export interface DailyEngagementApproval {
    by: string | null; // UUID do UserCompany aprovador
    byName?: string | null; // nome do aprovador (whenLoaded approvedByUserCompany)
    at: string | null; // ISO 8601
}

export interface DailyEngagement {
    id: string;
    companyId: string;
    employeeId: string;
    workloadId: string | null;
    date: string | null; // YYYY-MM-DD
    type: DailyEngagementType;
    status: DailyEngagementStatus;
    workedMinutes: number;
    expectedMinutes: number;
    balanceMinutes: number;
    diariaValue: number | null; // cast 'double' no back
    note: string | null;
    draftedBy: string | null;
    approval: DailyEngagementApproval;
    timeEntries?: TimeEntry[]; // whenLoaded('timeEntries')
    employee?: Employee; // whenLoaded('employee')
}

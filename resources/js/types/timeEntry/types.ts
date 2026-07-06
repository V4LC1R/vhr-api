/**
 * Resource (resposta da API) — TimeEntryResource.
 * Enums vivem aqui (const array = fonte única p/ tipo e schema).
 */
export const TIME_ENTRY_TYPES = ['entry', 'exit'] as const;
export type TimeEntryType = (typeof TIME_ENTRY_TYPES)[number];

export const TIME_ENTRY_SOURCES = ['manual', 'device'] as const;
export type TimeEntrySource = (typeof TIME_ENTRY_SOURCES)[number];

export interface TimeEntry {
    id: string;
    dailyEngagementId: string;
    punchedAt: string; // ISO 8601 em UTC
    type: TimeEntryType;
    source: TimeEntrySource;
    note: string | null;
}

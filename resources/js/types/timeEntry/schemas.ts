import { z } from 'zod';
import { TIME_ENTRY_TYPES } from './types';

/**
 * Request (entrada) — TimeEntryData.
 * ⚠️ `punchedAt` em camelCase (espelha o DTO). Vai com fuso; o back grava em UTC.
 */
export const timeEntrySchema = z.object({
    employeeId: z.uuid(),
    punchedAt: z.string(),
    type: z.enum(TIME_ENTRY_TYPES),
    note: z.string().nullable(),
});
export type TimeEntryPayload = z.infer<typeof timeEntrySchema>;

import { z } from 'zod';
import { TIME_ENTRY_TYPES } from './types';

/**
 * Request (entrada) — TimeEntryData.
 * ⚠️ `punched_at` em snake_case (espelha o DTO). Vai com fuso; o back grava em UTC.
 */
export const timeEntrySchema = z.object({
    employeeId: z.uuid(),
    punched_at: z.string(),
    type: z.enum(TIME_ENTRY_TYPES),
    note: z.string().nullable(),
});
export type TimeEntryPayload = z.infer<typeof timeEntrySchema>;

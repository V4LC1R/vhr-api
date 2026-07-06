import { z } from 'zod';
import { DAILY_ENGAGEMENT_TYPES } from './types';

/**
 * Request (entrada) — DailyEngagementData. Representa o CREATE.
 */
export const dailyEngagementSchema = z.object({
    type: z.enum(DAILY_ENGAGEMENT_TYPES),
    note: z.string().nullable(),
});
export type DailyEngagementPayload = z.infer<typeof dailyEngagementSchema>;

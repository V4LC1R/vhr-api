import { z } from 'zod';

/**
 * Request (entrada) — WorkloadData.
 * ⚠️ Chaves em snake_case: espelham exatamente as propriedades do DTO no back.
 * Representa o CREATE; use `.partial()` p/ UPDATE.
 */
export const workloadSchema = z.object({
    description: z.string().nonempty('Informe a descrição'),
    monthly_hours: z.number().int(),
    weekly_hours: z.number().int(),
    entry_time: z.string(),
    left_time: z.string(),
    interval_start_at: z.string().nullable(),
    interval_end_at: z.string().nullable(),
});
export type WorkloadPayload = z.infer<typeof workloadSchema>;

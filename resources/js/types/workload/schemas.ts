import { z } from 'zod';

/**
 * Request (entrada) — WorkloadData.
 * ⚠️ Chaves em camelCase: espelham exatamente as propriedades do DTO no back.
 * Representa o CREATE; use `.partial()` p/ UPDATE.
 */
export const workloadSchema = z.object({
    description: z.string().nonempty('Informe a descrição'),
    monthlyHours: z.number().int(),
    weeklyHours: z.number().int(),
    entryTime: z.string(),
    leftTime: z.string(),
    intervalStartAt: z.string().nullable(),
    intervalEndAt: z.string().nullable(),
});
export type WorkloadPayload = z.infer<typeof workloadSchema>;

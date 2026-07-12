import { z } from 'zod';

/**
 * Request (entrada) — WorkloadData.
 * ⚠️ Chaves em camelCase: espelham exatamente as propriedades do DTO no back.
 * Representa o CREATE; use `.partial()` p/ UPDATE.
 */
export const workloadSchema = z
    .object({
        description: z.string().nonempty('Informe a descrição'),
        monthlyHours: z.coerce.number().int().min(1, 'Informe as horas mensais'),
        weeklyHours: z.coerce.number().int().min(1, 'Informe as horas semanais'),
        entryTime: z.string().nonempty('Informe o horário de entrada'),
        leftTime: z.string().nonempty('Informe o horário de saída'),
        intervalStartAt: z.string().nonempty('Informe o início do intervalo'),
        intervalEndAt: z.string().nonempty('Informe o fim do intervalo'),
    })
    .refine((data) => data.weeklyHours <= data.monthlyHours, {
        message: 'As horas semanais não podem exceder as horas mensais',
        path: ['weeklyHours'],
    })
    .refine((data) => data.leftTime > data.entryTime, {
        message: 'O horário de saída deve ser posterior ao de entrada',
        path: ['leftTime'],
    })
    .refine((data) => data.intervalStartAt >= data.entryTime && data.intervalStartAt < data.leftTime, {
        message: 'O início do intervalo deve estar entre a entrada e a saída',
        path: ['intervalStartAt'],
    })
    .refine((data) => data.intervalEndAt > data.intervalStartAt && data.intervalEndAt <= data.leftTime, {
        message: 'O fim do intervalo deve estar entre o início do intervalo e a saída',
        path: ['intervalEndAt'],
    });
export type WorkloadPayload = z.infer<typeof workloadSchema>;

import { z } from 'zod';
import { DAILY_ENGAGEMENT_TYPES } from '@/types/dailyEngagement/types';

/** Formulário de tipo do dia (exceção: folga, feriado, atestado, falta). */
export const exceptionSchema = z.object({
    type: z.enum(DAILY_ENGAGEMENT_TYPES, { message: 'Informe o tipo do dia' }),
    note: z
        .string()
        .trim()
        .nonempty('Informe a observação')
        .max(255, 'A observação não pode passar de 255 caracteres'),
});
export type ExceptionPayload = z.infer<typeof exceptionSchema>;

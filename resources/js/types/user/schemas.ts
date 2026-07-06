import { z } from 'zod';

/**
 * Request (entrada) — UserData. Representa o CREATE; use `.partial()` p/ UPDATE.
 */
export const userSchema = z.object({
    email: z.email('E-mail inválido'),
    password: z.string().min(8, 'A senha deve ter ao menos 8 caracteres'),
    status: z.string().optional(),
});
export type UserPayload = z.infer<typeof userSchema>;

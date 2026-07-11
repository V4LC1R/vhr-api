import { z } from 'zod';

/**
 * Request (entrada) — PersonData. Representa o CREATE; use `.partial()` p/ UPDATE.
 */
export const personSchema = z.object({
    cpf: z.string().length(11, 'Informe um CPF válido'),
    name: z.string().nonempty('Informe o nome'),
    email: z.email('E-mail inválido'),
    cellphone: z.string().nonempty('Informe o celular'),
});
export type PersonPayload = z.infer<typeof personSchema>;

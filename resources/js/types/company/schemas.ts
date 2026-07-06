import { z } from 'zod';

/**
 * Request (entrada) — CompanyData. Representa o CREATE; use `.partial()` p/ UPDATE.
 */
export const companySchema = z.object({
    name: z.string().nonempty('Informe o nome da empresa'),
    cnpj: z.string().nonempty('Informe o CNPJ'),
});
export type CompanyPayload = z.infer<typeof companySchema>;

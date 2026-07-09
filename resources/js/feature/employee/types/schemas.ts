import { z } from 'zod';
import { EMPLOYMENT_STATUSES, EMPLOYMENT_TYPES } from '@/types/employment/types';

/**
 * Request (entrada) — EmployeeData (camelCase). Representa o CREATE.
 */
export const employeeSchema = z.object({
    companyId: z.uuid(),
    personId: z.uuid(),
    workloadId: z.uuid(),
    kind: z.enum(EMPLOYMENT_TYPES),
    status: z.enum(EMPLOYMENT_STATUSES),
});
export type EmployeePayload = z.infer<typeof employeeSchema>;

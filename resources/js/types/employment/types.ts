import type { Workload } from '@/feature/workload/types/types';

/**
 * Resource somente-leitura — EmploymentResource. Não há EmploymentData (request)
 * no back, por isso esta pasta NÃO tem schemas.ts/mappers.ts.
 *
 * Os enums do módulo Job vivem aqui (const array = fonte única p/ tipo e schema)
 * e são reaproveitados por Employee.
 */
export const EMPLOYMENT_STATUSES = ['hired', 'experience', 'left'] as const;
export type EmploymentStatus = (typeof EMPLOYMENT_STATUSES)[number];

export const EMPLOYMENT_TYPES = ['clt', 'dayli', 'temporary', 'freelancer'] as const;
export type EmploymentType = (typeof EMPLOYMENT_TYPES)[number];

export interface Employment {
    id: string;
    employeeId: string;
    workloadId: string;
    kind: EmploymentType; // resource: 'kind'
    status: EmploymentStatus;
    registerAt: string | null; // ISO 8601
    leftAt: string | null;
    workload?: Workload; // whenLoaded('workload')
}

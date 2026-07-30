import type { Person } from '@/feature/person/types/types';
import { Workload } from '@/feature/workload/types';
import { PaginatedResponse, PaginateParams } from '@/types/paginate-requests';

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

export type Employee = {
    id: string;
    companyId: string;
    personId: string;
    registerNumber: string;
    person?: Person;
    activeEmployment?: Employment;
    employments?:Employment[]
}

export type EmployeeListFilters = {
    name?: string;
    status?: string;
    kind?: string;
    registerAt?: string;
};

export type EmployeeParamsPaginated = PaginateParams<EmployeeListFilters>
export type EmployeeResponsePaginated = PaginatedResponse<Employee>


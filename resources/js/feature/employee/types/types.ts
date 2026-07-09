import type { Person } from '@/types/person/types';
import type { Employment } from '@/types/employment/types';
import { PaginatedResponse, PaginateParams } from '@/types/paginate-requests';

export type EmployeeListFilters = {
    name?: string;
    status?: string;
    kind?: string;
    registerAt?: string;
};

export type EmployeeParamsPaginated = PaginateParams<EmployeeListFilters>
export type EmployeeResponsePaginated = PaginatedResponse<Employee>
export interface Employee {
    id: string;
    companyId: string;
    personId: string;
    registerNumber: string;
    person?: Person; // whenLoaded('person')
    activeEmployment?: Employment; // whenLoaded('activeEmployment')
}

import type { Person } from '@/types/person/types';
import type { Employment } from '@/types/employment/types';

/**
 * Resource (resposta da API) — EmployeeResource.
 * `person` e `activeEmployment` só vêm quando carregados (whenLoaded).
 */
export interface Employee {
    id: string;
    companyId: string;
    personId: string;
    registerNumber: string;
    person?: Person; // whenLoaded('person')
    activeEmployment?: Employment; // whenLoaded('activeEmployment')
}

import { EMPLOYMENT_STATUSES, EMPLOYMENT_TYPES } from '@/types/employment/types';
import type { Employee } from './types';
import type { EmployeePayload } from './schemas';
import { email } from 'zod';

export function mapToForm(resource: Omit<Employee, 'id'>): EmployeePayload {
    return {
        isProbationary:resource.activeEmployment?.status ==='experience',
        personId: resource.personId,
        workloadId: resource.activeEmployment?.workloadId ?? '',
        kind: resource.activeEmployment?.kind ?? EMPLOYMENT_TYPES[0],
        status: resource.activeEmployment?.status ?? EMPLOYMENT_STATUSES[0],
        person:{
            name:resource.person?.name,
            email:resource.person?.email,
            pixKey:resource.person?.pixKey ?? '',
            cellphone:resource.person?.cellphone ?? '',
            cpf:resource.person?.cpf ?? '',
        }
    };
}

export function mapToApi(form: EmployeePayload): EmployeePayload {
    return {
        isProbationary:form.isProbationary,
        personId: form.personId,
        workloadId: form.workloadId,
        kind: form.kind,
        status: form.status,
        person:{
            name:form?.person.name,
            email:form?.person.email,
            pixKey:form?.person.pixKey ?? '',
            cellphone:form?.person.cellphone ?? '',
            cpf:form?.person.cpf?.replace(/[^\d]+/g, ''),
            birthdate:form?.person.birthdate,
        }
        
    };
}

import { EMPLOYMENT_STATUSES, EMPLOYMENT_TYPES } from '@/types/employment/types';
import type { Employee } from './types';
import type { EmployeePayload } from './schemas';
import { email } from 'zod';

export function mapToForm(resource: Omit<Employee, 'id'>): EmployeePayload {
    return {
        companyId: resource.companyId,
        personId: resource.personId,
        workloadId: resource.activeEmployment?.workloadId ?? '',
        kind: resource.activeEmployment?.kind ?? EMPLOYMENT_TYPES[0],
        status: resource.activeEmployment?.status ?? EMPLOYMENT_STATUSES[0],
        name:resource.person?.name,
        email:resource.person?.email,
        pixKey:resource.person?.pixKey ?? '',
        cellphone:resource.person?.cellphone ?? '',
        cpf:resource.person?.cpf ?? '',
    };
}

export function mapToApi(form: EmployeePayload): EmployeePayload {
    return {
        companyId: form.companyId,
        personId: form.personId,
        workloadId: form.workloadId,
        kind: form.kind,
        status: form.status,
        name:form?.name,
        email:form?.email,
        pixKey:form?.pixKey ?? '',
        cellphone:form?.cellphone ?? '',
        cpf:form?.cpf,
        birthdate:form?.birthdate,
    };
}

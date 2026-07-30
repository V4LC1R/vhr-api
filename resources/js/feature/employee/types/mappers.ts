import { EMPLOYMENT_STATUSES, EMPLOYMENT_TYPES } from '@/types/employment/types';
import { isValid, parse } from 'date-fns';
import { format } from "@react-input/mask";
import type { Employee } from './types';
import type { EmployeePayload } from './schemas';
import { email } from 'zod';

export function parseBirthdate(value: string | null | undefined): Date | undefined {
    if (!value) return undefined

    const date = parse(value.slice(0, 10), 'yyyy-MM-dd', new Date())

    return isValid(date) ? date : undefined
}

const CELLPHONE_MASK = { mask: "(##) # ####-####", replacement: { '#': /\d/ } };


export function formatCellphone(value: string | undefined): string {
    if (!value) return ''

    return format(value, CELLPHONE_MASK)
}

const CPF_MASK = { mask: "###.###.###-##", replacement: { '#': /\d/ } };


export function formatCpf(value: string | undefined): string {
    if (!value) return ''

    return format(value, CPF_MASK)
}

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
            cellphone:formatCellphone(resource.person?.cellphone) ?? '',
            cpf:formatCpf(resource.person?.cpf  ?? ''),
            birthdate:parseBirthdate(resource.person?.birthdate),
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

export function mapToApiForUpdate(form: EmployeePayload) {
    return {
        isProbationary:form.isProbationary,
        
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

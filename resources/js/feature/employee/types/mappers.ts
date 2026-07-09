import { EMPLOYMENT_STATUSES, EMPLOYMENT_TYPES } from '@/types/employment/types';
import type { Employee } from './types';
import type { EmployeePayload } from './schemas';

export function mapToForm(resource: Employee): EmployeePayload {
    return {
        companyId: resource.companyId,
        personId: resource.personId,
        workloadId: resource.activeEmployment?.workloadId ?? '',
        kind: resource.activeEmployment?.kind ?? EMPLOYMENT_TYPES[0],
        status: resource.activeEmployment?.status ?? EMPLOYMENT_STATUSES[0],
    };
}

export function mapToApi(form: EmployeePayload): EmployeePayload {
    return {
        companyId: form.companyId,
        personId: form.personId,
        workloadId: form.workloadId,
        kind: form.kind,
        status: form.status,
    };
}

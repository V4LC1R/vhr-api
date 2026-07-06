import type { TimeEntry } from './types';
import type { TimeEntryPayload } from './schemas';

/**
 * mapToForm: resource (resposta) -> valores do formulário.
 * mapToApi:  valores do formulário -> payload de request.
 *
 * `employeeId` não vem no TimeEntryResource (vem do contexto do dia): fica vazio
 * no mapToForm — preencher a partir do DailyEngagement/tela.
 */
export function mapToForm(resource: TimeEntry): TimeEntryPayload {
    return {
        employeeId: '',
        punched_at: resource.punchedAt,
        type: resource.type,
        note: resource.note,
    };
}

export function mapToApi(form: TimeEntryPayload): TimeEntryPayload {
    return {
        employeeId: form.employeeId,
        punched_at: form.punched_at,
        type: form.type,
        note: form.note,
    };
}

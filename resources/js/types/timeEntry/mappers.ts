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
        punchedAt: resource.punchedAt,
        type: resource.type,
        note: resource.note,
    };
}

export function mapToApi(form: TimeEntryPayload): TimeEntryPayload {
    return {
        employeeId: form.employeeId,
        punchedAt: form.punchedAt,
        type: form.type,
        note: form.note,
    };
}

import type { DailyEngagement } from './types';
import type { DailyEngagementPayload } from './schemas';

/**
 * mapToForm: resource (resposta) -> valores do formulário.
 * mapToApi:  valores do formulário -> payload de request.
 */
export function mapToForm(resource: DailyEngagement): DailyEngagementPayload {
    return {
        type: resource.type,
        note: resource.note,
    };
}

export function mapToApi(form: DailyEngagementPayload): DailyEngagementPayload {
    return {
        type: form.type,
        note: form.note,
    };
}

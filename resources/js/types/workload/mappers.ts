import type { Workload } from './types';
import type { WorkloadPayload } from './schemas';

/**
 * mapToForm: resource (resposta) -> valores do formulário.
 * mapToApi:  valores do formulário -> payload de request.
 *
 * Traduz o camelCase do resource para o snake_case do request e achata o
 * objeto `interval` em `interval_start_at`/`interval_end_at`.
 */
export function mapToForm(resource: Workload): WorkloadPayload {
    return {
        description: resource.description,
        monthly_hours: resource.monthlyHours,
        weekly_hours: resource.weeklyHours,
        entry_time: resource.entryTime,
        left_time: resource.leftTime,
        interval_start_at: resource.interval.startAt,
        interval_end_at: resource.interval.endAt,
    };
}

export function mapToApi(form: WorkloadPayload): WorkloadPayload {
    return {
        description: form.description,
        monthly_hours: form.monthly_hours,
        weekly_hours: form.weekly_hours,
        entry_time: form.entry_time,
        left_time: form.left_time,
        interval_start_at: form.interval_start_at,
        interval_end_at: form.interval_end_at,
    };
}

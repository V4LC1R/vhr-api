import type { Workload } from './types';
import type { WorkloadPayload } from './schemas';

/**
 * mapToForm: resource (resposta) -> valores do formulário.
 * mapToApi:  valores do formulário -> payload de request.
 *
 * Resource e request agora são camelCase; o mapeamento só achata o objeto
 * `interval` do resource em `intervalStartAt`/`intervalEndAt`.
 */
export function mapToForm(resource: Workload): WorkloadPayload {
    return {
        description: resource.description,
        monthlyHours: resource.monthlyHours,
        weeklyHours: resource.weeklyHours,
        entryTime: resource.entryTime,
        leftTime: resource.leftTime,
        intervalStartAt: resource.interval.startAt ?? "",
        intervalEndAt: resource.interval.endAt ?? "",
    };
}

export function mapToApi(form: WorkloadPayload): WorkloadPayload {
    return {
        description: form.description,
        monthlyHours: form.monthlyHours,
        weeklyHours: form.weeklyHours,
        entryTime: form.entryTime,
        leftTime: form.leftTime,
        intervalStartAt: form.intervalStartAt,
        intervalEndAt: form.intervalEndAt,
    };
}

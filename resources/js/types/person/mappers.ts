import type { Person } from './types';
import type { PersonPayload } from './schemas';

/**
 * mapToForm: resource (resposta) -> valores do formulário.
 * mapToApi:  valores do formulário -> payload de request.
 */
export function mapToForm(resource: Person): PersonPayload {
    return {
        name: resource.name,
        email: resource.email,
        cellphone: resource.cellphone,
    };
}

export function mapToApi(form: PersonPayload): PersonPayload {
    return {
        name: form.name,
        email: form.email,
        cellphone: form.cellphone,
    };
}

import type { User } from './types';
import type { UserPayload } from './schemas';

/**
 * mapToForm: resource (resposta) -> valores do formulário.
 * mapToApi:  valores do formulário -> payload de request.
 *
 * `password` não vem no resource: fica em branco na edição.
 */
export function mapToForm(resource: User): UserPayload {
    return {
        email: resource.email,
        password: '',
        status: resource.status,
    };
}

export function mapToApi(form: UserPayload): UserPayload {
    return {
        email: form.email,
        password: form.password,
        status: form.status,
    };
}

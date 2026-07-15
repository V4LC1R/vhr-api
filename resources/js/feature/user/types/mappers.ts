import type { User } from './types';
import type { CreateUserPayload, UpdateUserPayload, UserRole } from './schemas';

/**
 * mapToForm: resource (resposta) -> valores do formulário de edição.
 * `password` não vem no resource: fica em branco (mantém a senha atual).
 * `role` vem do vínculo da empresa ativa — a listagem já escopa `companies`
 * pra essa empresa, então há no máximo um vínculo relevante aqui.
 */
export function mapToForm(resource: User): UpdateUserPayload {
    return {
        email: resource.email,
        password: '',
        role: resource.companies?.[0]?.role as UserRole | undefined,
        status: (resource.status as 'active' | 'inactive') ?? 'active',
    };
}

export function mapCreateToApi(form: CreateUserPayload) {
    return {
        email: form.email,
        password: form.password,
        role: form.role,
        personId: form.personId || undefined,
    };
}

/** `password` vazio = campo omitido → back mantém a senha atual (regra `nullable`). */
export function mapUpdateToApi(form: UpdateUserPayload) {
    const { password, ...rest } = form;
    return password ? { ...rest, password } : rest;
}

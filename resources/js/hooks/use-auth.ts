import { usePage } from '@inertiajs/react';

/**
 * Contexto de autenticação/sessão compartilhado pelo Inertia (`HandleInertiaRequests::share`).
 *
 * - `user`: identidade de login (`{ id, email }`) ou `null` quando não autenticado.
 * - `current`: empresa ativa com nome, roles e permissões — `null` enquanto
 *   nenhuma empresa foi selecionada (é o gatilho da tela de seleção de empresa).
 *
 * Os dados são reativos: a cada visita do Inertia os props são refetchados, então
 * trocar a empresa ativa (SetActiveCompany) reflete aqui na navegação seguinte.
 */
export function useAuth() {
    const { auth } = usePage().props;

    const user = auth.user;
    const current = auth.current;
    const companies = auth.companies;

    /** Possui a role informada na empresa ativa. */
    const hasRole = (role: string) => current?.roles.includes(role) ?? false;

    /** Possui a permissão informada na empresa ativa. */
    const can = (permission: string) => current?.permissions.includes(permission) ?? false;

    return {
        user,
        current,
        companies,
        isAuthenticated: user !== null,
        hasCompany: current !== null,
        hasRole,
        can,
    };
}

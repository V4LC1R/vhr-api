import { http } from "@inertiajs/react";

/**
 * Redireciona pro login quando a sessão expira durante uma chamada `/api`.
 *
 * As rotas `/api` respondem 401 quando não há sessão — o back NÃO redireciona
 * requisição de API de propósito (ver `app/Exceptions/Handler.php`). Este
 * handler global do `useHttp`/`http` intercepta esse 401 uma única vez e joga
 * o usuário pra tela de login, sem precisar tratar em cada chamada.
 *
 * Exceção: 401 vindo das telas de auth (ex.: `/api/auth/login` com credencial
 * errada) NÃO redireciona — senão o formulário perderia a mensagem de erro.
 */
export function registerHttpInterceptors(): void {
    http.onError((error) => {
        if (!("response" in error) || error.response.status !== 401) {
            return;
        }

        // Já estamos numa tela de auth: o 401 é do próprio fluxo de login,
        // não de sessão expirada. Deixa o formulário exibir o erro.
        if (window.location.pathname.startsWith("/auth")) {
            return;
        }

        window.location.href = "/auth/login";
    });
}

/**
 * `error.response.data` chega como STRING (JSON cru, não parseado) — não um
 * objeto — por isso precisa de `JSON.parse` antes de ler seus campos.
 */
function parseErrorData(error: unknown): Record<string, unknown> | null {
    if (!error || typeof error !== "object" || !("response" in error)) {
        return null;
    }

    let data = (error as { response?: { data?: unknown } }).response?.data;

    if (typeof data === "string") {
        try {
            data = JSON.parse(data);
        } catch {
            return null;
        }
    }

    return data && typeof data === "object" ? (data as Record<string, unknown>) : null;
}

/**
 * Extrai a mensagem de erro do back a partir do erro rejeitado por `useHttp`.
 */
export function extractErrorMessage(error: unknown, fallback: string): string {
    const data = parseErrorData(error);

    if (data && "message" in data && typeof data.message === "string") {
        return data.message;
    }

    return fallback;
}

/**
 * Extrai os erros de validação por campo (formato padrão do Laravel:
 * `{ errors: { campo: ["mensagem"] } }`) a partir do erro rejeitado por `useHttp`.
 */
export function extractFieldErrors(error: unknown): Record<string, string> {
    const data = parseErrorData(error);
    const errors = data?.errors;

    if (!errors || typeof errors !== "object") {
        return {};
    }

    const fieldErrors: Record<string, string> = {};
    for (const [field, messages] of Object.entries(errors as Record<string, unknown>)) {
        if (Array.isArray(messages) && typeof messages[0] === "string") {
            fieldErrors[field] = messages[0];
        }
    }
    return fieldErrors;
}

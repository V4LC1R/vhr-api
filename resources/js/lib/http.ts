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

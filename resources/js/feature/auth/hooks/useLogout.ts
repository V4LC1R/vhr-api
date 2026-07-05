import { useHttp } from "@inertiajs/react";

type LogoutForm = Record<string, never>;

/**
 * Encerra a sessão (`POST /api/auth/logout`) e leva de volta pra tela de login.
 *
 * Usa navegação "hard" (`window.location`) de propósito: um `router.visit` do
 * Inertia preservaria o histórico do SPA, então o botão "voltar" reexibiria o
 * dashboard a partir do cache do Inertia sem passar pelo servidor.
 */
export function useLogout() {
    const { post, processing } = useHttp<LogoutForm>();

    async function logout() {
        await post("/api/auth/logout");
        window.location.href = "/auth/login";
    }

    return { logout, processing };
}

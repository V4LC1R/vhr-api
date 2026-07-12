import { useHttp } from "@inertiajs/react";

type LogoutForm = Record<string, never>;

export function useLogout() {
    const { post, processing } = useHttp<LogoutForm>();

    async function logout() {
        await post("/api/auth/logout");
        window.location.href = "/auth/login";
    }

    return { logout, processing };
}

import { useHttp } from "@inertiajs/react";
import { LoginForm, LoginResponse } from "../types";

export function useLogin() {
    const { post, setData, processing,response } = useHttp<LoginForm, LoginResponse>();

    async function login(data: LoginForm, options?: Parameters<typeof post>[1]) {
        setData(data);
        return await post("/api/auth/login", options); 
    }

    return { login, processing, response };
}
import { useForm } from "react-hook-form";
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema";

import { loginSchema } from "../schemas/login-schema";
import { LoginForm } from "../types";
import { useLogin } from "./useLogin";
import { router } from "@inertiajs/react";

export function useLoginForm() {
    const {login,processing} = useLogin()
    
    const form = useForm<LoginForm>({
        resolver: standardSchemaResolver(loginSchema),
        defaultValues: {
            email: "",
            password: "",
        },
    });

    const onSubmit = async (data:LoginForm) => {
        try {
            const session = await login(data)
            console.log(session)
            router.visit('/dashboard')
        } catch (error) {
            console.log(error)
        }
    }

    return {
        form,
        onSubmit,
        processing
    };
}

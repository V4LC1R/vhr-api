import { SubmitHandler, useForm } from "react-hook-form";
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema";

import { loginSchema } from "../schemas/login-schema";
import { LoginForm } from "../types";
import { useLogin } from "./useLogin";

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
        } catch (error) {
            
        }
    }

    return {
        form,
        onSubmit,
        processing
    };
}

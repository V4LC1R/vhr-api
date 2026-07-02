import { LoginView } from "@/feature/auth/views/login-view";
import AuthLayout from "@/layouts/AuthLayout";
import { ReactNode } from "react";

export default function Login() {
    return(<LoginView/>);
}

Login.layout = (page: ReactNode) => <AuthLayout>{page}</AuthLayout>;
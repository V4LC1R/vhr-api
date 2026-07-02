import { ForgotPassword } from "@/feature/auth/views/forgot-password";
import { LoginView } from "@/feature/auth/views/login-view";
import AuthLayout from "@/layouts/AuthLayout";
import { ReactNode } from "react";

export default function Login() {
    return(<ForgotPassword/>);
}

Login.layout = (page: ReactNode) => <AuthLayout>{page}</AuthLayout>;
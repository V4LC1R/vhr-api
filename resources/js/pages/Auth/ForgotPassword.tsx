import { ForgotPassword } from "@/feature/auth/views/forgot-password";
import AuthLayout from "@/layouts/AuthLayout";
import { ReactNode } from "react";

export default function Login() {
    return(<ForgotPassword/>);
}

Login.layout = (page: ReactNode) => <AuthLayout>{page}</AuthLayout>;
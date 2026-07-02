import { ResetPasswordView } from "@/feature/auth/views/reset-password";
import AuthLayout from "@/layouts/AuthLayout";
import { ReactNode } from "react";

export default function ResetPassword() {
    return (<ResetPasswordView/>);
}

ResetPassword.layout = (page: ReactNode) => <AuthLayout>{page}</AuthLayout>;

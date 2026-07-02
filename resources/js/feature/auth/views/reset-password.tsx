import { useForm, type Control } from "react-hook-form";
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema";
import { useHttp, usePage, Link } from "@inertiajs/react";
import { z } from "zod";

import { Field, FieldGroup } from "@/components/ui/field";
import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";

const resetPasswordSchema = z
    .object({
        password: z.string().nonempty("Informe a nova senha!"),
        password_confirmation: z.string().nonempty("Confirme a nova senha!"),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: "As senhas não conferem!",
        path: ["password_confirmation"],
    });

type ResetPasswordForm = z.infer<typeof resetPasswordSchema>;

export function ResetPasswordView() {
    const { token } = usePage<{ token: string }>().props;
    const { post, setData, processing } = useHttp<ResetPasswordForm & { token: string }, { message?: string }>();

    const form = useForm<ResetPasswordForm>({
        resolver: standardSchemaResolver(resetPasswordSchema),
        defaultValues: { password: "", password_confirmation: "" },
    });

    const onSubmit = async (data: ResetPasswordForm) => {
        try {
            setData({ ...data, token });
            await post("/api/auth/reset-password");
        } catch (error) {
            // erros de validação são refletidos pelo response do useHttp
        }
    };

    return (
        <RHF.Form
            className="w-full h-full md:h-130 max-w-sm rounded-xl border bg-card p-8 shadow-lg shadow-gray-200/60 flex flex-col justify-center"
            form={form}
            onSubmit={onSubmit}
        >
            <div className="mb-6 flex flex-col gap-1 text-center">
                <h1 className="text-2xl font-semibold tracking-tight">Redefinir senha</h1>
                <p className="text-sm text-muted-foreground">Escolha uma nova senha para sua conta</p>
            </div>

            <FieldGroup>
                <RHF.Input
                    control={form.control as unknown as Control}
                    name="password"
                    label="Nova senha"
                    type="password"
                    className="h-10"
                />

                <RHF.Input
                    control={form.control as unknown as Control}
                    name="password_confirmation"
                    label="Confirmar senha"
                    type="password"
                    className="h-10"
                />

                <Field orientation="vertical">
                    <Button disabled={processing} className="h-10 cursor-pointer" type="submit">
                        {processing ? "Redefinindo..." : "Redefinir senha"}
                    </Button>
                </Field>

                <p className="text-center text-sm text-muted-foreground">
                    Lembrou a senha?{" "}
                    <Link href="auth/login" className="underline underline-offset-4 hover:text-primary">
                        Voltar para o login
                    </Link>
                </p>
            </FieldGroup>
        </RHF.Form>
    );
}

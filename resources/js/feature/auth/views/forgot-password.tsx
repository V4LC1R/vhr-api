import { useForm, type Control } from "react-hook-form";
import { standardSchemaResolver } from "@hookform/resolvers/standard-schema";
import { Link, useHttp } from "@inertiajs/react";
import { z } from "zod";

import { Field, FieldGroup } from "@/components/ui/field";
import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";

const forgotPasswordSchema = z.object({
    email: z.string().nonempty("Um email e necessario!"),
});

type ForgotPasswordForm = z.infer<typeof forgotPasswordSchema>;

export function ForgotPassword() {
    const { post, setData, processing } = useHttp<ForgotPasswordForm, { message?: string }>();

    const form = useForm<ForgotPasswordForm>({
        resolver: standardSchemaResolver(forgotPasswordSchema),
        defaultValues: { email: "" },
    });

    const onSubmit = async (data: ForgotPasswordForm) => {
        try {
            setData(data);
            await post("/api/auth/forgot-password");
        } catch (error) {
          
        }
    };

    return (
        <RHF.Form
            className="w-full h-full md:h-130 max-w-sm rounded-xl border bg-card p-8 shadow-lg flex flex-col justify-center"
            form={form}
            onSubmit={onSubmit}
        >
            <div className="mb-6 flex flex-col gap-1 text-center">
                <h1 className="text-2xl font-semibold tracking-tight">Esqueceu a sua senha?</h1>
                <p className="text-sm text-muted-foreground">Insira o email cadastrado para recuperar</p>
            </div>

            <FieldGroup>
                <RHF.Input
                    control={form.control as unknown as Control}
                    name="email"
                    label="E-mail cadastrado"
                    type="email"
                    className="h-10"
                />

                <Field orientation="vertical">
                    <Button disabled={processing} className="h-10 cursor-pointer" type="submit">
                        {processing ? "Enviando..." : "Enviar link de recuperação"}
                    </Button>
                </Field>

                <p className="text-center text-sm text-muted-foreground">
                    Lembrou a senha?{" "}
                    <Link href="/auth/login" className="underline underline-offset-4 hover:text-primary">
                        Voltar para o login
                    </Link>
                </p>
            </FieldGroup>
        </RHF.Form>
    );
}

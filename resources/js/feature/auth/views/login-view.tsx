import { Field, FieldGroup } from "@/components/ui/field";
import { LoginForm } from "../components/forms/login-form";
import { useLoginForm } from "../hooks/useLoginForm";
import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";
import { logo } from "@/lib/utils";

export function LoginView() {
    const { form, onSubmit, processing } = useLoginForm();

    return (
        <RHF.Form
            className="w-full h-full md:h-130 max-w-sm rounded-xl border bg-card p-8 shadow-lg flex flex-col justify-center"
            form={form}
            onSubmit={onSubmit}
        >
            <div className="mb-6 flex flex-col gap-1 text-center items-center">
                <img src={logo.dark} alt="VHR" className="size-25 shrink-0 dark:hidden" />
                <img src={logo.gold} alt="VHR" className="hidden size-25 shrink-0 dark:block" />
                <h1 className="text-2xl font-semibold tracking-tight">Bem-vindo de volta</h1>
                <p className="text-sm text-muted-foreground">Acesse sua conta para continuar</p>
            </div>

            <FieldGroup>
                <LoginForm />
                <Field orientation="vertical">
                    <Button disabled={processing} className="h-10 cursor-pointer" type="submit">
                        {processing ? "Entrando..." : "Login"}
                    </Button>
                </Field>
            </FieldGroup>
        </RHF.Form>
    );
}

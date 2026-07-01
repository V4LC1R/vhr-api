
import { Field, FieldGroup } from "@/components/ui/field";
import { LoginForm } from "../components/forms/login-form";
import { useLoginForm } from "../hooks/useLoginForm";
import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";

export function LoginView() {
    const {form,onSubmit,processing} = useLoginForm()

    return (
        <RHF.Form 
            className="w-100 mx-auto"
            form={form}
            onSubmit={onSubmit}
        >

            <FieldGroup className="p-2">
                <LoginForm/>
                <Field orientation="vertical">
                    <Button disabled={processing} className="h-10 cursor-pointer" type="submit">Login</Button>
                </Field>
            </FieldGroup>
        </RHF.Form>
    );
}


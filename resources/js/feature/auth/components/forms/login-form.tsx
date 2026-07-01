import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Field, FieldGroup, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { useFormContext } from "react-hook-form";

export function LoginForm() {
    const {control}= useFormContext()
    
    return (
        <>
            <RHF.Input
                control={control}
                name="email"
                label="E-mail"
                type="email"
                className="h-10"
            />

            <RHF.Input
                control={control}
                name="password"
                label="Senha"
                type="password"
                className="h-10"
            />
           
            <div className="flex flex-row w-full">
                <Field orientation="horizontal">
                    <Checkbox
                        id="checkout-7j9-same-as-shipping-wgm"
                        defaultChecked
                    />
                    <FieldLabel
                        htmlFor="checkout-7j9-same-as-shipping-wgm"
                        className="font-normal"
                    >
                        Manter logado
                    </FieldLabel>
                </Field>

                <div className="w-60">
                    <a href="/forgot-password" className="text-sm underline">Esqueci minha senha!</a>
                </div>
            </div>
        </>
    )
}
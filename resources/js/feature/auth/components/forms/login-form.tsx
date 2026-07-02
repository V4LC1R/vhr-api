import { RHF } from "@/components/rhf-fields";
import { Checkbox } from "@/components/ui/checkbox";
import { FieldLabel } from "@/components/ui/field";
import { Link } from "@inertiajs/react";

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
           
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <Checkbox id="remember" defaultChecked />
                    <FieldLabel htmlFor="remember" className="font-normal">
                        Manter logado
                    </FieldLabel>
                </div>

                <Link
                    href="/forgot-password"
                    className="text-sm underline underline-offset-4 hover:text-primary"
                >
                    Esqueci minha senha!
                </Link>
            </div>
        </>
    )
}
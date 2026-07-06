import { useState } from "react";
import type { Control } from "react-hook-form";

import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";
import { Icon } from "@iconify/react";

type Props = {
    control: Control;
    name:string
};

export function PasswordInput({ control,name }: Props) {
    const [visible, setVisible] = useState(false);

    return (
        <div className="relative w-full">
            <RHF.Input
                control={control}
                name={name}
                label="Senha"
                type={visible ? "text" : "password"}
                className="h-10 pr-10"
            />

            {/* Toggle alinhado à caixa do input: top-7 = label (~20px) + gap-2 (8px);
                h-10 + items-center centra o botão na altura do input (o erro fica abaixo). */}
            <div className="absolute right-1 top-7 flex h-10 items-center">
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-lg"
                    aria-label={visible ? "Ocultar senha" : "Mostrar senha"}
                    aria-pressed={visible}
                    onClick={() => setVisible((v) => !v)}
                    className="text-muted-foreground hover:text-foreground"
                >
                    <Icon icon={visible ? "solar:eye-broken" : "solar:eye-closed-bold-duotone"} />
                </Button>
            </div>
        </div>
    );
}

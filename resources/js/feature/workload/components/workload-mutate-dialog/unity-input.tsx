import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";
import { Icon } from "@iconify/react";
import { useFormContext, useWatch } from "react-hook-form";

type Props = {
    name: string
    label?:string
    min?: number
    step?: number
}

export function UnityInput({ name, min = 0, step = 1, label}: Props) {
    const { control, setValue } = useFormContext()
    const value = useWatch({ control, name })

    const handleStep = (delta: number) => {
        const next = (Number(value) || 0) + delta
        if (next < min) return

        setValue(name, next, { shouldValidate: true, shouldDirty: true })
    }

    return (
        <div className="w-full flex flex-col gap-2">
            <h4 className="font-semibold">{label}</h4>
            <div className="flex flex-row gap-2 justify-center items-center">
                <Button type="button" className="w-10 h-10 text-primary" variant="outline" onClick={() => handleStep(-step)}>
                    <Icon icon="solar:minus-circle-bold-duotone"/>
                </Button>

                <RHF.Input
                    control={control}
                    name={name}
                    type="number"
                    className="text-center text-primary font-bold py-7 text-4xl md:text-3xl"
                    containerClassName="w-full h-auto"
                />

                <Button type="button" className="w-10 h-10 text-primary" variant="outline" onClick={() => handleStep(step)}>
                    <Icon icon="solar:add-circle-bold-duotone"/>
                </Button>
            </div>
        </div>
    )
}
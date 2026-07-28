import { RHF } from "@/components/rhf-fields";
import { Button } from "@/components/ui/button";
import { Icon } from "@iconify/react";
import { useFormContext, useWatch } from "react-hook-form";

type Props = {
    name: string
    min?: number
    step?: number
}

export function UnityInput({ name, min = 0, step = 1 }: Props) {
    const { control, setValue } = useFormContext()
    const value = useWatch({ control, name })

    const handleStep = (delta: number) => {
        const next = (Number(value) || 0) + delta
        if (next < min) return

        setValue(name, next, { shouldValidate: true, shouldDirty: true })
    }

    return (
        <div className="flex flex-row gap-2 justify-center">
            <Button type="button" className="w-10 h-10" variant="outline" onClick={() => handleStep(-step)}>
                <Icon icon="solar:minus-circle-bold-duotone"/>
            </Button>

            <RHF.Input
                control={control}
                name={name}
                type="number"
                className="text-center"
                containerClassName="w-70"
            />

            <Button type="button" className="w-10 h-10" variant="outline" onClick={() => handleStep(step)}>
                <Icon icon="solar:add-circle-bold-duotone"/>
            </Button>
        </div>
    )
}
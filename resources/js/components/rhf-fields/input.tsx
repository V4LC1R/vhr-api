import { Controller, type Control } from "react-hook-form";

import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";

type Props = {
    name: string;
    label?: string;
    control: Control;
    containerClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
} & Omit<React.ComponentProps<typeof Input>, "name">;

export function RHFInput({
    name,
    label,
    control,
    containerClassName,
    labelClassName,
    errorClassName,
    ...props
}: Props) {
    return (
        <Controller
            control={control}
            name={name}
            render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid} className={cn(containerClassName)}>
                    {label && (
                        <FieldLabel htmlFor={name} className={cn(labelClassName)}>
                            {label}
                        </FieldLabel>
                    )}

                    <Input
                        id={name}
                        aria-invalid={fieldState.invalid}
                        {...field}
                        {...props}
                    />

                    <FieldError
                        className={cn(errorClassName)}
                        errors={fieldState.error ? [fieldState.error] : undefined}
                    />
                </Field>
            )}
        />
    );
}

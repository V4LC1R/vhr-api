import { Controller, type Control, type FieldValues, type Path } from "react-hook-form";
import { useMask } from '@react-input/mask';

import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";

type Props<T extends FieldValues> = {
    name: Path<T>;
    label?: string;
    control: Control<T>;
    containerClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
    mask?:string
    replacement?: Record<string, RegExp>
} & Omit<React.ComponentProps<typeof Input>, "name">;

export function RHFInputMask<T extends FieldValues = FieldValues>({
    name,
    label,
    control,
    containerClassName,
    labelClassName,
    errorClassName,
    mask = '',
    replacement = { '#': /\d/ },
    ...props
}: Props<T>) {
    const inputRef = useMask({
        mask: mask,
        replacement,
        showMask:false
    })
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
                        ref={inputRef}
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

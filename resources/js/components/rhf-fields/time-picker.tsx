import { Controller, type Control, type FieldValues, type Path } from "react-hook-form";

import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { TimePicker } from "@/components/ui/time-picker";
import { cn } from "@/lib/utils";

type Props<T extends FieldValues> = {
    name: Path<T>;
    label?: string;
    control: Control<T>;
    containerClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
    /** Converte o valor do form (ex: "08:00:00") pro formato "HH:mm" exibido no picker. */
    format?: (fieldValue: any) => string | null;
    /** Converte o "HH:mm" selecionado no picker de volta pro valor salvo no form. */
    parse?: (time: string | null) => any;
} & Omit<React.ComponentProps<typeof TimePicker>, "value" | "onChange" | "id">;

export function RHFTimePicker<T extends FieldValues = FieldValues>({
    name,
    label,
    control,
    containerClassName,
    labelClassName,
    errorClassName,
    format,
    parse,
    ...props
}: Props<T>) {
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

                    <TimePicker
                        id={name}
                        value={format ? format(field.value) : (field.value ?? null)}
                        onChange={(time) => field.onChange(parse ? parse(time) : time)}
                        disabled={field.disabled}
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

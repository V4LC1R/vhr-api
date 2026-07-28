import { Control, Controller, FieldValues, Path } from "react-hook-form";
import { Field, FieldError, FieldLabel } from "@/components/ui/field";
import { Checkbox } from "../ui/checkbox";
import { cn } from "@/lib/utils";

type Props<T extends FieldValues> = {
    name: Path<T>;
    label?: string;
    control: Control<T>;
    containerClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
} & Omit<React.ComponentProps<typeof Checkbox>, "name" | "checked" | "onCheckedChange">;

export function RHFCheckbox<T extends FieldValues = FieldValues>({
    name,
    label,
    control,
    containerClassName,
    labelClassName,
    errorClassName,
    ...props
}: Props<T>) {
    return (
        <Controller
            control={control}
            name={name}
            render={({ field, fieldState }) => (
                <Field data-invalid={fieldState.invalid} className={cn("gap-1.5", containerClassName)}>
                    <div className="flex items-center gap-2">
                        <Checkbox
                            id={name}
                            aria-invalid={fieldState.invalid}
                            checked={!!field.value}
                            onCheckedChange={field.onChange}
                            onBlur={field.onBlur}
                            name={field.name}
                            ref={field.ref}
                            {...props}
                        />

                        {label && (
                            <FieldLabel htmlFor={name} className={cn("cursor-pointer", labelClassName)}>
                                {label}
                            </FieldLabel>
                        )}
                    </div>

                    <FieldError
                        className={cn(errorClassName)}
                        errors={fieldState.error ? [fieldState.error] : undefined}
                    />
                </Field>
            )}
        />
    );
}
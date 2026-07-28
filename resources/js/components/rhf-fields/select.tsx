import { Control, Controller, FieldValues, Path } from "react-hook-form";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "../ui/select";
import { Field, FieldError, FieldLabel } from "../ui/field";
import { cn } from "@/lib/utils";

type Props<T extends FieldValues, TOption> = {
    name: Path<T>;
    label?: string;
    control: Control<T>;
    options: TOption[];
    itemRender: (option: TOption) => React.ReactNode;
    getOptionValue: (option: TOption) => string | number;
    containerClassName?: string;
    triggerClassName?: string;
    labelClassName?: string;
    errorClassName?: string;
} & Omit<React.ComponentProps<typeof Select>, "name" | "children" | "value" | "onValueChange">;


export function RHFSelect<T extends FieldValues = FieldValues, TOption = unknown>({
    name,
    label,
    control,
    options,
    itemRender,
    getOptionValue,
    containerClassName,
    triggerClassName,
    labelClassName,
    errorClassName,
    ...props
}: Props<T, TOption>) {
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

                    <Select
                        value={field.value ?? null}
                        onValueChange={field.onChange}
                        {...props}
                    >
                        <SelectTrigger id={name} aria-invalid={fieldState.invalid} className={cn("w-full",triggerClassName)}>
                            <SelectValue>
                                {(value: string | number) => {
                                    const selected = options.find((option) => getOptionValue(option) === value)
                                    return selected ? itemRender(selected) : undefined
                                }}
                            </SelectValue>
                        </SelectTrigger>

                        <SelectContent>
                            {options.map((option) => {
                                const value = getOptionValue(option)

                                return (
                                    <SelectItem className="items-center justify-center flex flex-row h-full w-full cursor-pointer" key={value} value={value}>
                                        {itemRender(option)}
                                    </SelectItem>
                                )
                            })}
                        </SelectContent>
                    </Select>

                    <FieldError
                        className={cn(errorClassName)}
                        errors={fieldState.error ? [fieldState.error] : undefined}
                    />
                </Field>
            )}
        />
    );
}
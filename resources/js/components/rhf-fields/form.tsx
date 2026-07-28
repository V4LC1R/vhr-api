import type { ReactNode } from "react";
import {
    FormProvider,
    type FieldValues,
    type SubmitHandler,
    type UseFormReturn,
} from "react-hook-form";

type Props<T extends FieldValues> = {
    form: UseFormReturn<T>;
    onSubmit: SubmitHandler<T>;
    children: ReactNode;
    stopPropagation?: boolean;
} & Omit<React.ComponentProps<"form">, "onSubmit">;

export function RHFForm<T extends FieldValues>({
    form,
    onSubmit,
    children,
    stopPropagation,
    ...props
}: Props<T>) {
    return (
        <FormProvider {...form}>
            <form
                onSubmit={(e) => {
                    if (stopPropagation) e.stopPropagation()
                    return form.handleSubmit(onSubmit)(e)
                }}
                {...props}
            >
                {children}
            </form>
        </FormProvider>
    );
}

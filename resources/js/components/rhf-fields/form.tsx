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
} & Omit<React.ComponentProps<"form">, "onSubmit">;

export function RHFForm<T extends FieldValues>({
    form,
    onSubmit,
    children,
    ...props
}: Props<T>) {
    return (
        <FormProvider {...form}>
            <form onSubmit={form.handleSubmit(onSubmit)} {...props}>
                {children}
            </form>
        </FormProvider>
    );
}

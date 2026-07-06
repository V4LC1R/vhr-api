import type { ComponentProps } from "react";
import { Clock } from "lucide-react";

import { cn, logo } from "@/lib/utils";

interface BrandProps extends ComponentProps<"span"> {
    name?: string;
}

export function Brand({ name = "VHR", className, ...props }: BrandProps) {
    return (
        <span
            className={cn(
                "inline-flex items-center gap-2 font-heading text-lg font-semibold tracking-tight",
                className,
            )}
            {...props}
        >
            <span className="flex size-7 items-center justify-center rounded-md bg-primary text-primary-foreground">
                <img src={logo.gold } alt="VHR" className="size-7 shrink-0 dark:hidden" />
                <img src={logo.dark} alt="VHR" className="hidden size-7 shrink-0 dark:block" />
            </span>
            {name}
        </span>
    );
}

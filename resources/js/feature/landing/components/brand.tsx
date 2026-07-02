import type { ComponentProps } from "react";
import { Clock } from "lucide-react";

import { cn } from "@/lib/utils";

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
                <Clock className="size-4" />
            </span>
            {name}
        </span>
    );
}

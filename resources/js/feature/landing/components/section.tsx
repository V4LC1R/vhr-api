import type { ComponentProps, ReactNode } from "react";

import { cn } from "@/lib/utils";
import { Badge } from "@/components/ui/badge";

interface SectionProps extends Omit<ComponentProps<"section">, "title"> {
    eyebrow?: ReactNode;
    title?: ReactNode;
    description?: ReactNode;
    align?: "left" | "center";
    containerClassName?: string;
}

/**
 * Wrapper de seção da landing: cuida do espaçamento vertical, do container
 * centralizado e do cabeçalho (eyebrow + título + descrição). O conteúdo real
 * de cada seção entra como children.
 */
export function Section({
    eyebrow,
    title,
    description,
    align = "center",
    className,
    containerClassName,
    children,
    ...props
}: SectionProps) {
    const hasHeader = Boolean(eyebrow || title || description);

    return (
        <section className={cn("scroll-mt-20 py-20 sm:py-28", className)} {...props}>
            <div className={cn("mx-auto w-full max-w-6xl px-6", containerClassName)}>
                {hasHeader && (
                    <div
                        className={cn(
                            "flex max-w-2xl flex-col gap-4",
                            align === "center" && "mx-auto items-center text-center",
                        )}
                    >
                        {eyebrow && (
                            <Badge variant="outline" className="text-muted-foreground">
                                {eyebrow}
                            </Badge>
                        )}
                        {title && (
                            <h2 className="font-heading text-3xl font-semibold tracking-tight text-balance sm:text-4xl">
                                {title}
                            </h2>
                        )}
                        {description && (
                            <p className="text-base text-pretty text-muted-foreground sm:text-lg">
                                {description}
                            </p>
                        )}
                    </div>
                )}

                {children && (hasHeader ? <div className="mt-14">{children}</div> : children)}
            </div>
        </section>
    );
}

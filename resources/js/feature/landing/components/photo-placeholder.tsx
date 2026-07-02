import type { ComponentProps } from "react";
import { ImageIcon } from "lucide-react";

import { cn } from "@/lib/utils";
import { AspectRatio } from "@/components/ui/aspect-ratio";

interface PhotoPlaceholderProps extends Omit<ComponentProps<"div">, "children"> {
    ratio?: number;
    label?: string;
    hint?: string;
}

/**
 * Espaço reservado para uma foto/print do produto. Troque por um <img /> quando
 * as imagens reais estiverem prontas — o formato (ratio) já fica garantido.
 */
export function PhotoPlaceholder({
    ratio = 16 / 9,
    label = "Espaço para foto",
    hint,
    className,
    ...props
}: PhotoPlaceholderProps) {
    return (
        <AspectRatio
            ratio={ratio}
            className={cn(
                "overflow-hidden rounded-xl border border-dashed border-border bg-muted/40",
                className,
            )}
            {...props}
        >
            <div className="absolute inset-0 flex flex-col items-center justify-center gap-2 p-4 text-center text-muted-foreground">
                <div className="flex size-11 items-center justify-center rounded-full bg-background/70 ring-1 ring-border">
                    <ImageIcon className="size-5" />
                </div>
                <span className="text-sm font-medium">{label}</span>
                {hint && <span className="max-w-xs text-xs text-muted-foreground/80">{hint}</span>}
            </div>
        </AspectRatio>
    );
}

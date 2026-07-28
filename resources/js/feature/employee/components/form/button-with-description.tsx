import { cn } from "@/lib/utils";
import { CheckIcon } from "lucide-react";
import { ReactNode } from "react";
import { unknown } from "zod";

type Props<T> = {
    onChange?:(value:T)=>void
    isSelected?:boolean
    value?:T
    label?:string,
    description?:string
    icon?:ReactNode
}
export function ButtonWithDescription<T>({
    onChange,
    value,
    label,
    description,
    icon,
    isSelected=false
}:Props<T>){
    return(
         <button
            type="button"
            onClick={() => onChange?.(value?? unknown as T)}
            aria-pressed={isSelected}
            className={cn(
                "cursor-pointer h-35 group relative flex aspect-square flex-col items-center justify-center gap-2 overflow-hidden rounded-xl border p-4 text-center transition-all duration-300 ease-out",
                "hover:-translate-y-1 hover:border-accent/60 hover:shadow-primary/20",
                "dark:hover:border-accent dark:hover:shadow-accent/20",
                "focus-visible:outline-none focus-visible:ring-3 focus-visible:ring-accent/50 dark:focus-visible:ring-accent/50",
                isSelected
                    ? "border-primary bg-accent/10  dark:border-primary dark:bg-accent/10 dark:shadow-primary/10"
                    : "border-border bg-card"
            )}
        >
            <span
                aria-hidden
                className={cn(
                    "pointer-events-none absolute inset-0 duration-300 dark:from-accent/15",
                    "dark:group-hover:opacity-100",
                    isSelected && "dark:opacity-100"
                )}
            />
            <div className="flex flex-row w-full items-center gap-2">
                <div
                    className={cn(
                        "relative z-10 flex size-10 items-center justify-center rounded-full transition-colors duration-300",
                        isSelected
                            ? " bg-[#9A7B33] text-background dark:bg-accent dark:text-accent-foreground"
                            : "bg-muted text-muted-foreground group-hover:text-[#9A7B33] dark:group-hover:bg-accent dark:group-hover:text-accent-foreground"
                    )}
                >
                    {icon}
                    
                </div>
                <span
                    className={cn(
                        "relative z-10 text-lg font-medium transition-colors duration-300",
                        isSelected
                            ? "text-primary dark:text-accent"
                            : "text-foreground group-hover:text-primary dark:group-hover:text-accent"
                    )}
                >
                    {label}
                </span>
                {isSelected && (
                    <span className=" flex size-4 items-center justify-center rounded-full bg-accent text-primary-foreground dark:bg-accent dark:text-accent-foreground">
                        <CheckIcon className="size-2.5" />
                    </span>
                )}
            </div>
            <span className="relative z-10 text-xs text-start text-muted-foreground">
                {description}
            </span>
        </button>
    )
}
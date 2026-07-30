import { cn } from "@/lib/utils";

type Props = {
    className?:string
}
export function Dot({className}:Props){
    return (
        <span className={cn("text-[6px]",className)}>&#9679;</span>
    )
}
import { Badge } from "@/components/ui/badge"
import { cn } from "@/lib/utils"
import { EmploymentType } from "@/types/employment/types"


export const EMPLOYMENT_TYPE_LABELS: Record<EmploymentType, string> = {
    clt: "CLT",
    dayli: "Diarista",
    temporary: "Temporário",
    freelancer: "Freelancer",
}

export const EMPLOYMENT_STYLE: Record<EmploymentType, string> = {
    clt: "text-primary border-success/20 bg-success/10",
    dayli: "text-warning border-warning/30 bg-warning/10",
    temporary: "text-info border-info/30 bg-info/10",
    freelancer: "",
}

type Props = {
    kind:EmploymentType
}

export function KindChip({kind}:Props){
    return(
        <Badge 
            className={cn("rounded-md p-2.5",EMPLOYMENT_STYLE[kind])} 
            variant="outline"
        >
            {EMPLOYMENT_TYPE_LABELS[kind]}
        </Badge>
    )
}
import { Badge } from "@/components/ui/badge"
import { cn } from "@/lib/utils"
import { EmploymentStatus } from "@/types/employment/types"


export const EMPLOYMENT_STATUS_LABELS: Record<EmploymentStatus, string> = {
   experience:'Experiencia',
   hired:'Ativo',
   left:' Demitido'
}

export const EMPLOYMENT_STATUS_STYLE: Record<EmploymentStatus, string> = {
    experience:'',
    hired:'bg-success/15 text-success border-success/15',
    left:'bg-negative border-negative border-negative/15'
}

type Props = {
    status:EmploymentStatus
}

export function StatusChip({status}:Props){
    return(
        <Badge 
            className={cn("rounded-2xl p-2.5",EMPLOYMENT_STATUS_STYLE[status])} 
            variant="outline"
        >
            <span className="text-[6px]">&#9679;</span> {EMPLOYMENT_STATUS_LABELS[status]}
        </Badge>
    )
}
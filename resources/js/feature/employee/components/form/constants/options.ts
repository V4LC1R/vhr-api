import { EmploymentType } from "@/feature/employee/types";

import {
    BriefcaseIcon,
    CalendarDaysIcon,
    HourglassIcon,
    UserRoundIcon,
    type LucideIcon,
} from "lucide-react"

export const CONTRACT_TYPES: { value: EmploymentType; label: string; description: string; icon: LucideIcon }[] = [
    {
        value: "clt",
        label: "CLT",
        description: "Vínculo formal, carteira assinada e direitos trabalhistas completos.",
        icon: BriefcaseIcon,
    },
    {
        value: "dayli",
        label: "Diarista",
        description: "Pagamento por dia trabalhado, sem vínculo fixo.",
        icon: CalendarDaysIcon,
    },
    {
        value: "temporary",
        label: "Temporário",
        description: "Contrato por prazo determinado, para demandas pontuais.",
        icon: HourglassIcon,
    },
    {
        value: "freelancer",
        label: "Freelancer",
        description: "Prestador autônomo, sem vínculo empregatício.",
        icon: UserRoundIcon,
    },
]
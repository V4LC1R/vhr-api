import {
    Building2,
    CalendarOff,
    CheckCircle2,
    FileText,
    Globe,
    ScanLine,
    type LucideIcon,
} from "lucide-react";

export interface Feature {
    icon: LucideIcon;
    title: string;
    description: string;
}

export const features: Feature[] = [
    {
        icon: ScanLine,
        title: "Digitalize a planilha de ponto",
        description:
            "Transforme as folhas de ponto de papel em registros digitais em poucos minutos. Chega de recalcular tudo na mão no fim do mês.",
    },
    {
        icon: CheckCircle2,
        title: "Aprovação de dias",
        description:
            "Revise os lançamentos, corrija o que precisar e aprove o dia com um clique. Só o que estiver aprovado entra no fechamento.",
    },
    {
        icon: FileText,
        title: "Relatório pronto pro contador",
        description:
            "No fim do mês o contador enxerga apenas os dias aprovados, com as horas já somadas e prontas para a folha de pagamento.",
    },
    {
        icon: Building2,
        title: "Várias empresas, um lugar só",
        description:
            "Gerencie mais de uma empresa na mesma conta, com colaboradores, permissões e equipes separados para cada uma.",
    },
    {
        icon: CalendarOff,
        title: "Controle de folgas",
        description:
            "Lance folgas e ausências direto no calendário do colaborador e mantenha o mês fechado sem furos nem dúvidas.",
    },
    {
        icon: Globe,
        title: "Cada ponto no horário certo",
        description:
            "Os registros são guardados em UTC e exibidos no fuso da sua empresa — sem aquele erro de uma hora pra mais ou pra menos.",
    },
];

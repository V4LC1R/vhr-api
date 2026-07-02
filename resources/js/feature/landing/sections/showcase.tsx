import { Check } from "lucide-react";

import { Badge } from "@/components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Section } from "../components/section";
import { PhotoPlaceholder } from "../components/photo-placeholder";

const steps = [
    {
        value: "lancamento",
        tab: "Lançar",
        step: "Passo 1",
        title: "Do papel pra tela em minutos",
        bullets: [
            "Digite os horários da planilha ou registre o dia direto no sistema",
            "As horas de cada dia são somadas automaticamente",
            "Folgas e ausências entram no mesmo calendário",
        ],
    },
    {
        value: "aprovacao",
        tab: "Aprovar",
        step: "Passo 2",
        title: "Revise e aprove antes de fechar",
        bullets: [
            "Veja rapidamente os dias que precisam de atenção",
            "Corrija um horário sem bagunçar o resto do mês",
            "Aprove o dia com um clique — o rascunho não vaza pro relatório",
        ],
    },
    {
        value: "relatorio",
        tab: "Relatório",
        step: "Passo 3",
        title: "O contador recebe tudo pronto",
        bullets: [
            "Apenas os dias aprovados aparecem no relatório",
            "Horas somadas e organizadas por colaborador",
            "Exporte e mande direto para a folha de pagamento",
        ],
    },
];

export function Showcase() {
    return (
        <Section
            id="como-funciona"
            className="bg-muted/30"
            eyebrow="Como funciona"
            title="Do papel ao relatório em três passos"
            description="O mesmo caminho que você já faz na planilha, só que organizado e sem retrabalho."
        >
            <Tabs defaultValue="lancamento" className="w-full">
                <TabsList className="mx-auto">
                    {steps.map((step) => (
                        <TabsTrigger key={step.value} value={step.value}>
                            {step.tab}
                        </TabsTrigger>
                    ))}
                </TabsList>

                {steps.map((step) => (
                    <TabsContent key={step.value} value={step.value} className="mt-10">
                        <div className="grid items-center gap-8 lg:grid-cols-2">
                            <div className="flex flex-col gap-4">
                                <Badge variant="secondary" className="w-fit">
                                    {step.step}
                                </Badge>
                                <h3 className="font-heading text-2xl font-semibold tracking-tight">
                                    {step.title}
                                </h3>
                                <ul className="flex flex-col gap-3">
                                    {step.bullets.map((bullet) => (
                                        <li key={bullet} className="flex items-start gap-3 text-muted-foreground">
                                            <Check className="mt-0.5 size-5 shrink-0 text-primary" />
                                            <span>{bullet}</span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                            <PhotoPlaceholder
                                ratio={4 / 3}
                                label={`Foto: ${step.tab}`}
                                hint="Print desta etapa dentro do sistema"
                            />
                        </div>
                    </TabsContent>
                ))}
            </Tabs>
        </Section>
    );
}

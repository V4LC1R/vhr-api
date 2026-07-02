import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Section } from "../components/section";
import { features } from "../data/features";

export function Features() {
    return (
        <Section
            id="funcionalidades"
            eyebrow="Funcionalidades"
            title="Tudo pra fechar o mês sem dor de cabeça"
            description="Do lançamento do ponto ao relatório do contador, cada etapa foi pensada pra quem cuida da folha no dia a dia."
        >
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {features.map(({ icon: Icon, title, description }) => (
                    <Card key={title} className="transition-colors hover:bg-muted/30">
                        <CardHeader>
                            <div className="flex size-10 items-center justify-center rounded-lg bg-primary/5 text-primary ring-1 ring-primary/10">
                                <Icon className="size-5" />
                            </div>
                            <CardTitle className="mt-3 text-base">{title}</CardTitle>
                        </CardHeader>
                        <CardContent className="text-muted-foreground">{description}</CardContent>
                    </Card>
                ))}
            </div>
        </Section>
    );
}

import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from "@/components/ui/accordion";
import { Section } from "../components/section";

const faqs = [
    {
        question: "Preciso instalar alguma coisa?",
        answer: "Não. O VHR funciona direto no navegador, no computador ou no celular. É só entrar e começar a usar.",
    },
    {
        question: "Já uso planilha. Dá pra migrar?",
        answer: "Dá sim. Você lança os pontos que já tem e, daí em diante, o sistema passa a somar tudo automaticamente pra você.",
    },
    {
        question: "O contador consegue acessar?",
        answer: "Sim. Ele enxerga apenas os dias já aprovados de cada mês, prontos pra folha, sem ver rascunhos ou dados soltos.",
    },
    {
        question: "Consigo controlar mais de uma empresa?",
        answer: "Consegue. Cada empresa tem seus próprios colaboradores, permissões e relatórios, tudo dentro da mesma conta.",
    },
];

export function Faq() {
    return (
        <Section
            id="faq"
            containerClassName="max-w-3xl"
            eyebrow="Dúvidas"
            title="Perguntas frequentes"
            description="As dúvidas mais comuns de quem está começando com o VHR."
        >
            <Accordion defaultValue={["faq-0"]} className="rounded-xl border bg-card px-5">
                {faqs.map((faq, index) => (
                    <AccordionItem key={faq.question} value={`faq-${index}`}>
                        <AccordionTrigger className="text-base">{faq.question}</AccordionTrigger>
                        <AccordionContent className="text-muted-foreground">{faq.answer}</AccordionContent>
                    </AccordionItem>
                ))}
            </Accordion>
        </Section>
    );
}

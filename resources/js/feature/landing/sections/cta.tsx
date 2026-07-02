import type { CSSProperties } from "react";
import { Link } from "@inertiajs/react";
import { ArrowRight } from "lucide-react";

import { Button } from "@/components/ui/button";

const highlight: CSSProperties = {
    background:
        "radial-gradient(60% 120% at 50% 0%, color-mix(in oklch, var(--primary-foreground) 10%, transparent), transparent 70%)",
};

export function Cta() {
    return (
        <section className="px-6 py-20 sm:py-24">
            <div className="relative mx-auto flex max-w-5xl flex-col items-center overflow-hidden rounded-3xl bg-primary px-6 py-16 text-center text-primary-foreground sm:px-12">
                <div aria-hidden className="pointer-events-none absolute inset-0" style={highlight} />

                <h2 className="relative font-heading text-3xl font-semibold tracking-tight text-balance sm:text-4xl">
                    Pronto pra largar a planilha de ponto?
                </h2>
                <p className="relative mt-4 max-w-xl text-pretty text-primary-foreground/80">
                    Comece a organizar as horas da sua equipe hoje mesmo. Leva poucos minutos pra
                    lançar o primeiro dia.
                </p>

                <div className="relative mt-8 flex flex-col gap-3 sm:flex-row">
                    <Button
                        size="lg"
                        variant="secondary"
                        className="h-11 px-5 text-base"
                        render={<Link href="/auth/login" />}
                    >
                        Começar agora
                        <ArrowRight />
                    </Button>
                    <Button
                        size="lg"
                        variant="ghost"
                        className="h-11 px-5 text-base text-primary-foreground hover:bg-primary-foreground/10 hover:text-primary-foreground"
                        render={<a href="#funcionalidades" />}
                    >
                        Ver funcionalidades
                    </Button>
                </div>
            </div>
        </section>
    );
}

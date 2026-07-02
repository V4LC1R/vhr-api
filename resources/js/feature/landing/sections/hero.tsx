import type { CSSProperties } from "react";
import { Link } from "@inertiajs/react";
import { ArrowRight } from "lucide-react";

import { Avatar, AvatarFallback, AvatarGroup, AvatarGroupCount } from "@/components/ui/avatar";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";
import { PhotoPlaceholder } from "../components/photo-placeholder";

const gridPattern: CSSProperties = {
    backgroundImage: "radial-gradient(circle at 1px 1px, var(--border) 1px, transparent 0)",
    backgroundSize: "28px 28px",
    WebkitMaskImage: "radial-gradient(ellipse 55% 55% at 50% 0%, #000 55%, transparent 100%)",
    maskImage: "radial-gradient(ellipse 55% 55% at 50% 0%, #000 55%, transparent 100%)",
};

const initials = ["MR", "JS", "AL", "CF"];

export function Hero() {
    return (
        <section className="relative overflow-hidden">
            <div aria-hidden className="pointer-events-none absolute inset-0 -z-10" style={gridPattern} />

            <div className="mx-auto w-full max-w-6xl px-6 pt-16 pb-20 sm:pt-24">
                <div className="mx-auto flex max-w-3xl flex-col items-center text-center">
                    <Badge variant="outline" className="gap-1.5 py-1">
                        <span className="inline-block size-1.5 rounded-full bg-primary" />
                        Feito para pequenas empresas
                    </Badge>

                    <h1 className="mt-6 font-heading text-4xl font-semibold tracking-tight text-balance sm:text-5xl md:text-6xl">
                        O controle de ponto da sua empresa, sem planilha bagunçada
                    </h1>

                    <p className="mt-5 max-w-2xl text-lg text-pretty text-muted-foreground">
                        O VHR digitaliza as folhas de ponto, organiza as horas de cada colaborador e
                        entrega o fechamento do mês pronto pro contador — tudo em português e no seu fuso.
                    </p>

                    <div className="mt-8 flex flex-col items-center gap-3 sm:flex-row">
                        <Button size="lg" className="h-11 px-5 text-base" render={<Link href="/auth/login" />}>
                            Começar agora
                            <ArrowRight />
                        </Button>
                        <Button
                            size="lg"
                            variant="outline"
                            className="h-11 px-5 text-base"
                            render={<a href="#funcionalidades" />}
                        >
                            Ver funcionalidades
                        </Button>
                    </div>

                    <div className="mt-8 flex items-center gap-3">
                        <AvatarGroup>
                            {initials.map((value) => (
                                <Avatar key={value}>
                                    <AvatarFallback>{value}</AvatarFallback>
                                </Avatar>
                            ))}
                            <Tooltip>
                                <TooltipTrigger render={<AvatarGroupCount />}>+</TooltipTrigger>
                                <TooltipContent>E muitas outras equipes que largaram a planilha</TooltipContent>
                            </Tooltip>
                        </AvatarGroup>
                        <p className="text-left text-sm text-muted-foreground">
                            Equipes que trocaram a planilha pelo VHR
                        </p>
                    </div>
                </div>

                <div className="relative mx-auto mt-14 max-w-5xl">
                    <PhotoPlaceholder
                        ratio={16 / 9}
                        label="Print do painel do VHR"
                        hint="Coloque aqui uma imagem do sistema (dashboard, lançamento de pontos, etc.)"
                        className="shadow-2xl shadow-foreground/10"
                    />
                </div>
            </div>
        </section>
    );
}

import { Link } from "@inertiajs/react";

import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";
import { ThemeToggle } from "@/components/theme/theme-toggle";
import { Brand } from "../components/brand";

const links = [
    { href: "#funcionalidades", label: "Funcionalidades" },
    { href: "#como-funciona", label: "Como funciona" },
    { href: "#roadmap", label: "Road-map" },
];

export function Navbar() {
    return (
        <header className="sticky top-0 z-50 border-b border-border/60 bg-background/80 backdrop-blur">
            <div className="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-4 px-6">
                <div className="flex items-center gap-2">
                    <Brand />
                    <Tooltip>
                        <TooltipTrigger
                            render={<Badge variant="secondary" className="cursor-default" />}
                        >
                            Beta
                        </TooltipTrigger>
                        <TooltipContent>
                            Em desenvolvimento ativo — novidades toda semana
                        </TooltipContent>
                    </Tooltip>
                </div>

                <nav className="hidden items-center gap-1 md:flex">
                    {links.map((link) => (
                        <Button
                            key={link.href}
                            variant="ghost"
                            size="sm"
                            render={<a href={link.href} />}
                        >
                            {link.label}
                        </Button>
                    ))}
                </nav>

                <div className="flex items-center gap-1.5">
                    <ThemeToggle />
                    <Button
                        variant="ghost"
                        size="sm"
                        className="hidden sm:inline-flex"
                        render={<Link href="/auth/login" />}
                    >
                        Entrar
                    </Button>
                    <Button size="sm" render={<Link href="/auth/login" />}>
                        Começar agora
                    </Button>
                </div>
            </div>
        </header>
    );
}

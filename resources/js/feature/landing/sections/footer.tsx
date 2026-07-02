import { Link } from "@inertiajs/react";

import { Separator } from "@/components/ui/separator";
import { Brand } from "../components/brand";

const columns = [
    {
        title: "Produto",
        links: [
            { href: "#funcionalidades", label: "Funcionalidades" },
            { href: "#como-funciona", label: "Como funciona" },
            { href: "#roadmap", label: "Road-map" },
        ],
    },
    {
        title: "Conta",
        links: [
            { href: "/auth/login", label: "Entrar" },
            { href: "/auth/login", label: "Começar agora" },
        ],
    },
];

interface FooterProps {
    appName?: string;
}

export function Footer({ appName = "VHR" }: FooterProps) {
    const year = new Date().getFullYear();

    return (
        <footer className="border-t bg-muted/20">
            <div className="mx-auto w-full max-w-6xl px-6 py-14">
                <div className="flex flex-col gap-10 sm:flex-row sm:justify-between">
                    <div className="max-w-xs">
                        <Brand name={appName} />
                        <p className="mt-3 text-sm text-muted-foreground">
                            Controle de ponto simples pra pequenas empresas. Sem planilha bagunçada.
                        </p>
                    </div>

                    <div className="flex gap-12">
                        {columns.map((column) => (
                            <div key={column.title} className="flex flex-col gap-3">
                                <span className="text-sm font-medium">{column.title}</span>
                                {column.links.map((link) => (
                                    <a
                                        key={link.label}
                                        href={link.href}
                                        className="text-sm text-muted-foreground transition-colors hover:text-foreground"
                                    >
                                        {link.label}
                                    </a>
                                ))}
                            </div>
                        ))}
                    </div>
                </div>

                <Separator className="my-8" />

                <div className="flex flex-col items-center justify-between gap-3 text-sm text-muted-foreground sm:flex-row">
                    <p>
                        © {year} {appName}. Todos os direitos reservados.
                    </p>
                    <Link href="/auth/login" className="transition-colors hover:text-foreground">
                        Acessar o sistema
                    </Link>
                </div>
            </div>
        </footer>
    );
}

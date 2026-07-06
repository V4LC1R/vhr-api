import AppLayout from "@/layouts/AppLayout";
import { logo } from "@/lib/utils";
import { ReactNode } from "react";

export default function Home(){
    return (
        <div className="w-full flex-1 flex flex-col items-center justify-center">
            <div className="flex flex-row items-center gap-1">
                <img src={logo.dark} alt="VHR" className="size-45 shrink-0 dark:hidden" />
                <img src={logo.gold} alt="VHR" className="hidden size-45 shrink-0 dark:block" />
                <div>
                    <h1 className="text-5xl font-semibold tracking-tight first-letter:text-accent">VHR</h1>
                    <p className="text-lg text-muted-foreground">Sistema Simplificado para</p>
                    <p className="text-lg text-muted-foreground">Gestão de Recursos Humanos</p>
                </div>
            </div>
        </div>
    )
}
Home.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
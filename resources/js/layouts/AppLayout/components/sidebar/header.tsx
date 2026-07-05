import { SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link } from '@inertiajs/react';
import { ChevronDown, Clock } from 'lucide-react';
import { CompanySwitcher } from '../company-switcher';

export function SidebarHeaderApp() {
    return (
        <SidebarHeader>
            <SidebarMenu>
                {/* ---- LOGO DO SISTEMA ---- */}
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" tooltip="VHR" render={<Link href="/dashboard" />}>
                        <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                            {/* Troque este ícone pela logo:
                                <img src="/logo.svg" alt="VHR" className="size-5" />  (arquivo em public/)
                                ou cole seu <svg> inline aqui */}
                            <Clock className="size-4" />
                        </div>
                        <div className="grid flex-1 text-left leading-tight">
                            <span className="truncate font-heading font-semibold">VHR</span>
                            <span className="truncate text-xs text-sidebar-foreground/60">Gestão de RH</span>
                        </div>
                    </SidebarMenuButton>
                </SidebarMenuItem>

                {/* ---- SELETOR DE EMPRESA (reutilizável na sidebar e no header) ---- */}
                <SidebarMenuItem>
                    <CompanySwitcher
                        trigger={
                            <SidebarMenuButton>
                                Select Workspace
                                <ChevronDown className="ml-auto" />
                            </SidebarMenuButton>
                        }
                    />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
    );
}

import { SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { logo } from '@/lib/utils';
import { Link } from '@inertiajs/react';

export function SidebarHeaderApp() {
    return (
        <SidebarHeader>
            <SidebarMenu>
                {/* ---- LOGO DO SISTEMA ---- */}
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" tooltip="VHR" render={<Link href="/dashboard" />}>
                        {/* Marca VHR — tinta escura no tema claro, branca no escuro */}
                        <img src={logo.dark} alt="VHR" className="size-8 shrink-0 dark:hidden" />
                        <img src={logo.gold} alt="VHR" className="hidden size-8 shrink-0 dark:block" />
                        <div className="grid flex-1 text-left leading-tight group-data-[collapsible=icon]:hidden">
                            <span className="truncate font-heading font-semibold">VHR</span>
                            <span className="truncate text-xs text-sidebar-foreground/60">Gestão de RH</span>
                        </div>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
    );
}

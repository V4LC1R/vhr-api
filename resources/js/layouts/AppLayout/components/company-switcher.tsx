import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { ReactElement } from 'react';

type CompanySwitcherProps = {
    /** Elemento que abre o dropdown — na sidebar um SidebarMenuButton, no header da app um Button */
    trigger: ReactElement;
};

export function CompanySwitcher({ trigger }: CompanySwitcherProps) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger render={trigger} />
            <DropdownMenuContent className="w-(--anchor-width)">
                {/* TODO: listar empresas do contexto compartilhado do Inertia (auth) e trocar a ativa via SetActiveCompany */}
                <DropdownMenuItem>
                    <span>Acme Inc</span>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

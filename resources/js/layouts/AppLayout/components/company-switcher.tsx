import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useSelectCompany } from '@/feature/auth/hooks/useSelectCompany';
import { useAuth } from '@/hooks/use-auth';
import { router } from '@inertiajs/react';
import { Building2, Check } from 'lucide-react';
import type { ReactElement } from 'react';

type CompanySwitcherProps = {
    /** Elemento que abre o dropdown — na sidebar um SidebarMenuButton, no header da app um Button */
    trigger: ReactElement;
};

export function CompanySwitcher({ trigger }: CompanySwitcherProps) {
    const { companies, current } = useAuth();
    const { selectCompany, processing } = useSelectCompany();

    const handleSelect = async (companyId: string) => {
        if (processing || companyId === current?.companyId) return;
        await selectCompany(companyId);
        // SetActiveCompany lê o companyId da sessão na próxima requisição → refetch dos shared props
        router.reload();
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger render={trigger} />
            <DropdownMenuContent align="start" className="min-w-56">
                <DropdownMenuGroup>
                    <DropdownMenuLabel>Empresas</DropdownMenuLabel>
                    {companies.length === 0 ? (
                        <DropdownMenuItem disabled>Nenhuma empresa</DropdownMenuItem>
                    ) : (
                        companies.map((company) => (
                            <DropdownMenuItem
                                key={company.companyId}
                                disabled={processing}
                                onClick={() => handleSelect(company.companyId)}
                            >
                                <Building2 />
                                <span className="flex-1 truncate">{company.name ?? '—'}</span>
                                {company.companyId === current?.companyId && <Check className="ml-auto" />}
                            </DropdownMenuItem>
                        ))
                    )}
                </DropdownMenuGroup>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { Icon } from '@iconify/react';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { navItems, type NavItem } from '../../config/nav';
import { useAuth } from '@/hooks/use-auth';

/**
 * Filtra a navegação pelas permissões da empresa ativa.
 *
 * - Item de folha: aparece se não tiver `permission` ou se o usuário a possuir.
 * - Grupo (com `children`): filtra os filhos e só aparece se sobrar ao menos um;
 *   a `permission` do próprio grupo é ignorada aqui (a visibilidade vem dos filhos).
 */
function filterNav(items: NavItem[], can: (permission: string) => boolean): NavItem[] {
    return items.reduce<NavItem[]>((acc, item) => {
        if (item.children?.length) {
            const children = item.children.filter((child) => !child.permission || can(child.permission));
            if (children.length) acc.push({ ...item, children });
        } else if (!item.permission || can(item.permission)) {
            acc.push(item);
        }
        return acc;
    }, []);
}

export function NavMain() {
    const url = usePage().url.split('?')[0];
    const isActive = (path: string) =>
        url === path || (path !== '/dashboard' && url.startsWith(`${path}/`));
    const { can } = useAuth();
    const items = filterNav(navItems, can);
    return (
        <SidebarGroup>
            <SidebarMenu className="gap-1">
                {items.map((item) =>
                    item.children?.length ? (
                        <NavGroup key={item.path} item={item} isActive={isActive} />
                    ) : (
                        <SidebarMenuItem key={item.path}>
                            <SidebarMenuButton
                                isActive={isActive(item.path)}
                                tooltip={item.label}
                                className="transition-[background-color,color,width,height,padding] duration-200"
                                render={<Link href={item.path} />}
                            >
                                <Icon icon={item.icon} />
                                <span>{item.label}</span>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    ),
                )}
            </SidebarMenu>
        </SidebarGroup>
    );
}

function NavGroup({ item, isActive }: { item: NavItem; isActive: (path: string) => boolean }) {
    const { state, isMobile } = useSidebar();
    const childActive = item.children!.some((child) => isActive(child.path));

    // Navbar colapsada (só desktop): os subitens viram um flyout flutuando ao lado da navbar.
    if (state === 'collapsed' && !isMobile) {
        return (
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger
                        render={
                            <SidebarMenuButton isActive={childActive}>
                                <Icon className='dark:text-accent' icon={item.icon} />
                                <span>{item.label}</span>
                            </SidebarMenuButton>
                        }
                    />
                    <DropdownMenuContent side="right" align="start" sideOffset={4} className="w-48">
                        <DropdownMenuGroup>
                            <DropdownMenuLabel>{item.label}</DropdownMenuLabel>
                            {item.children!.map((child) => (
                                <DropdownMenuItem
                                    key={child.path}
                                    className="transition-colors duration-200"
                                    render={<Link href={child.path} />}
                                >
                                    <Icon className='dark:text-accent' icon={child.icon} />
                                    <span >{child.label}</span>
                                </DropdownMenuItem>
                            ))}
                        </DropdownMenuGroup>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        );
    }

    return (
        <Collapsible defaultOpen={childActive} render={<SidebarMenuItem />}>
            <CollapsibleTrigger
                render={
                    <SidebarMenuButton
                        tooltip={item.label}
                        isActive={childActive}
                        className="group/collapsible transition-[background-color,color,width,height,padding] duration-200"
                    >
                        <Icon className='dark:text-accent' icon={item.icon} />
                        <span>{item.label}</span>
                        <ChevronRight className="ml-auto transition-transform group-data-[panel-open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                }
            />
            <CollapsibleContent className="h-(--collapsible-panel-height) overflow-hidden transition-[height] duration-200 ease-out data-starting-style:h-0 data-ending-style:h-0">
                <SidebarMenuSub>
                    {item.children!.map((child) => (
                        <SidebarMenuSubItem key={child.path}>
                            <SidebarMenuSubButton
                                isActive={isActive(child.path)}
                                className="transition-colors duration-200"
                                render={<Link href={child.path} />}
                            >
                                <Icon className='dark:text-accent ' icon={child.icon} />
                                <span  className=''>{child.label}</span>
                            </SidebarMenuSubButton>
                        </SidebarMenuSubItem>
                    ))}
                </SidebarMenuSub>
            </CollapsibleContent>
        </Collapsible>
    );
}

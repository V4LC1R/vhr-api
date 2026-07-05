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

type Props = {
    permissions:string[]
}

export function NavMain({}) {
    const url = usePage().url.split('?')[0];
    const isActive = (path: string) => url === path || url.startsWith(`${path}/`);
    const {can} = useAuth()
    return (
        <SidebarGroup>
            <SidebarMenu className="gap-1">
                {navItems.map((item) =>
                    item.children?.length ? (
                        <NavGroup key={item.path} item={item} isActive={isActive} />
                    ) : (
                        <SidebarMenuItem key={item.path}>
                            <SidebarMenuButton
                                isActive={isActive(item.path)}
                                tooltip={item.label}
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
                                <Icon icon={item.icon} />
                                <span>{item.label}</span>
                            </SidebarMenuButton>
                        }
                    />
                    <DropdownMenuContent side="right" align="start" sideOffset={4} className="w-48">
                        <DropdownMenuGroup>
                            <DropdownMenuLabel>{item.label}</DropdownMenuLabel>
                            {item.children!.map((child) => (
                                <DropdownMenuItem key={child.path} render={<Link href={child.path} />}>
                                    <Icon icon={child.icon} />
                                    <span>{child.label}</span>
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
                    <SidebarMenuButton tooltip={item.label} isActive={childActive} className="group/collapsible">
                        <Icon icon={item.icon} />
                        <span>{item.label}</span>
                        <ChevronRight className="ml-auto transition-transform group-data-[panel-open]/collapsible:rotate-90" />
                    </SidebarMenuButton>
                }
            />
            <CollapsibleContent>
                <SidebarMenuSub>
                    {item.children!.map((child) => (
                        <SidebarMenuSubItem key={child.path}>
                            <SidebarMenuSubButton isActive={isActive(child.path)} render={<Link href={child.path} />}>
                                <Icon icon={child.icon} />
                                <span>{child.label}</span>
                            </SidebarMenuSubButton>
                        </SidebarMenuSubItem>
                    ))}
                </SidebarMenuSub>
            </CollapsibleContent>
        </Collapsible>
    );
}

import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { Icon } from '@iconify/react';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { navItems, type NavItem } from '../../config/nav';

export function NavMain() {
    const url = usePage().url.split('?')[0];
    const isActive = (path: string) => url === path || url.startsWith(`${path}/`);

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
    const childActive = item.children!.some((child) => isActive(child.path));

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

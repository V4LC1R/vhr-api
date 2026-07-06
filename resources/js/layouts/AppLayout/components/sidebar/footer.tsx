import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    SidebarFooter,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from "@/components/ui/sidebar";
import { useLogout } from "@/feature/auth/hooks/useLogout";
import { useAuth } from "@/hooks/use-auth";
import { Icon } from "@iconify/react";
import { ChevronsUpDown } from "lucide-react";

export function SidebarFooterApp() {
    const { user, current } = useAuth();
    const { logout, processing } = useLogout();
    const { isMobile } = useSidebar();

    const email = user?.email ?? "";
    const initial = email.charAt(0).toUpperCase() || "?";
    const name = current?.name ?? (email.split("@")[0] || "Usuário");

    return (
        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <DropdownMenu>
                        <DropdownMenuTrigger
                            render={
                                <SidebarMenuButton
                                    size="lg"
                                    className="data-popup-open:bg-sidebar-accent data-popup-open:text-sidebar-accent-foreground"
                                >
                                    <Avatar className="size-8 rounded-lg">
                                        <AvatarFallback className="rounded-lg dark:text-accent">{initial}</AvatarFallback>
                                    </Avatar>
                                    <div className="grid flex-1 text-left text-sm leading-tight group-data-[collapsible=icon]:hidden">
                                        <span className="truncate font-medium dark:text-accent">{name}</span>
                                        <span className="truncate text-xs text-sidebar-foreground/60">{email}</span>
                                    </div>
                                    <ChevronsUpDown className="ml-auto size-4 group-data-[collapsible=icon]:hidden" />
                                </SidebarMenuButton>
                            }
                        />
                        <DropdownMenuContent
                            className="min-w-56 rounded-lg"
                            side={isMobile ? "bottom" : "right"}
                            align="end"
                        >
                            <div className="flex items-center gap-2 px-1.5 py-1.5 text-left text-sm">
                                <Avatar className="size-8 rounded-lg">
                                    <AvatarFallback className="rounded-lg className='dark:text-accent'">{initial}</AvatarFallback>
                                </Avatar>
                                <div className="grid flex-1 text-left text-sm leading-tight">
                                    <span className="truncate font-medium">{name}</span>
                                    <span className="truncate text-xs text-muted-foreground">{email}</span>
                                </div>
                            </div>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem variant="destructive" disabled={processing} onClick={logout}>
                                <Icon icon="solar:exit-bold-duotone" />
                                Sair
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    );
}

import { Sidebar, SidebarContent, SidebarTrigger } from "@/components/ui/sidebar";
import { SidebarHeaderApp } from "./header";
import { SidebarFooterApp } from "./footer";
import { NavMain } from "./nav-main";
import { cn } from "@/lib/utils";

export function AppSidebar() {
    const triggerDarkMode = 'dark:text-white dark:hover:text-background dark:hover:bg-accent/90'
    const baseTriggerStyle = 'cursor-pointer absolute h-5.5 w-5.5 right-[-16px] top-[38px]'
    return (
       <Sidebar collapsible="icon" className="relative">
            <SidebarTrigger className={cn(baseTriggerStyle,triggerDarkMode," bg-input text-primary/90 hover:text-white hover:bg-primary/90")}/>
            <SidebarHeaderApp/>
            <SidebarContent>
                <NavMain />
            </SidebarContent>
            <SidebarFooterApp/>
        </Sidebar>
    )
}
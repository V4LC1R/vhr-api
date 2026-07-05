import { Sidebar, SidebarContent } from "@/components/ui/sidebar";
import { SidebarHeaderApp } from "./header";
import { SidebarFooterApp } from "./footer";
import { NavMain } from "./nav-main";


export function AppSidebar() {
    return (
       <Sidebar>
            <SidebarHeaderApp/>
            <SidebarContent>
                <NavMain />
            </SidebarContent>
            <SidebarFooterApp/>
        </Sidebar>
    )
}
import { ThemeToggle } from "@/components/theme/theme-toggle";
import { SidebarMenuButton, SidebarTrigger } from "@/components/ui/sidebar";
import { useAuth } from "@/hooks/use-auth";
import { CompanySwitcher } from "../company-switcher";
import { ChevronDown } from "lucide-react";

export function HeaderApp() {
    const { current, companies } = useAuth()
    const canSwitch = companies.length > 1
    const companyName = current?.company?.name ?? companies[0]?.name ?? null

    return (
        <div className="w-full p-2 border-b-sidebar-border border-b border-dashed flex flex-row items-center justify-between sticky">
            <SidebarTrigger className="cursor-pointer"/>
            <div className="flex flex-row items-center gap-2">
                {canSwitch ? (
                    <CompanySwitcher
                        trigger={
                            <SidebarMenuButton>
                                {companyName ?? 'Selecionar empresa'}
                                <ChevronDown className="ml-auto" />
                            </SidebarMenuButton>
                        }
                    />
                ) : (
                    companyName && <span className="px-2 text-sm font-medium">{companyName}</span>
                )}
                <ThemeToggle />
            </div>
        </div>
    )
}
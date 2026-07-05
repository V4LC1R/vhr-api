import { SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import type { PropsWithChildren } from 'react';
import { AppSidebar } from './components/sidebar';

export default function AppLayout({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen bg-background text-foreground">
            <SidebarProvider>
                <AppSidebar/>
                <SidebarTrigger />
                <div>

                    {children}
                </div>
            </SidebarProvider>
            
        </div>
    );
}

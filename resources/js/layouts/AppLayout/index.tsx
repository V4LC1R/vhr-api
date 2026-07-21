import { SidebarProvider } from '@/components/ui/sidebar';
import type { PropsWithChildren } from 'react';
import { AppSidebar } from './components/sidebar';
import { HeaderApp } from './components/header';

export default function AppLayout({ children }: PropsWithChildren) {

    return (
        <div className="h-svh overflow-hidden bg-background text-foreground">
            <SidebarProvider className="h-full overflow-hidden">
                <AppSidebar/>
                <div className='flex h-full min-h-0 flex-1 flex-col overflow-hidden'>
                    {children}
                </div>
            </SidebarProvider>
        </div>
    );
}

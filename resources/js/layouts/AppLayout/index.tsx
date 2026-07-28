import { SidebarProvider } from '@/components/ui/sidebar';
import type { PropsWithChildren } from 'react';
import { AppSidebar } from './components/sidebar';

export default function AppLayout({ children }: PropsWithChildren) {

    return (
        <div className="h-svh overflow-hidden bg-background text-foreground">
            <SidebarProvider className="h-full ">
                <AppSidebar/>
                <div className='flex h-full min-h-0 flex-1 flex-col '>
                    {children}
                </div>
            </SidebarProvider>
        </div>
    );
}

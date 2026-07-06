import { SidebarProvider } from '@/components/ui/sidebar';
import type { PropsWithChildren } from 'react';
import { AppSidebar } from './components/sidebar';
import { HeaderApp } from './components/header';

export default function AppLayout({ children }: PropsWithChildren) {

    return (
        <div className="min-h-screen bg-background text-foreground ">
            <SidebarProvider>
                <AppSidebar/>
                <div className='flex flex-1 flex-col'>
                    <HeaderApp/>
                    {children}
                </div>
            </SidebarProvider>
            
        </div>
    );
}

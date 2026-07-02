import { ThemeToggleButton } from '@/components/theme/theme-toggle-button';
import type { PropsWithChildren } from 'react';

export default function AuthLayout({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen w-full p-5">

            <div className="flex w-full md:w-[55%] md:min-w-110 items-center justify-center p-6 md:p-10 flex-col relative">
                {children}

                <ThemeToggleButton className="absolute bottom-5 right-5 z-50" />
            </div>

            <div className="hidden w-full bg-primary md:block rounded-md overflow-hidden"/>

        </div>
    );
}

import type { PropsWithChildren } from 'react';

export default function GuestLayout({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen flex items-center justify-center bg-background text-foreground">
            {children}
        </div>
    );
}

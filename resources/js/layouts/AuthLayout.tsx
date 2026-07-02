import type { PropsWithChildren } from 'react';

export default function AuthLayout({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen w-full">
            <div className="flex w-full md:w-[55%] md:min-w-110 items-center justify-center p-6 md:p-10">
                {children}
            </div>

            <div className="hidden w-full bg-primary md:block" />
        </div>
    );
}

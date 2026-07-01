import type { PropsWithChildren } from 'react';

export default function AuthLayout({ children }: PropsWithChildren) {
    return (
        <div className="min-h-screen min-w-screen flex flex-row items-center">
            <div className='w-full h-full'>
                {children}
            </div>

            <div className='w-full h-full hidden md:block bg-green-400'>
                
            </div>
        </div>
    );
}

import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import GuestLayout from '@/layouts/GuestLayout';

export default function Welcome({ appName }: { appName: string }) {
    return (
        <>
            <Head title="Welcome" />
            <div className="flex flex-col items-center justify-center gap-4">
                <h1 className="text-3xl font-bold">{appName} — Inertia v3 ✓</h1>
                <Button>shadcn funcionando</Button>
            </div>
        </>
    );
}

Welcome.layout = (page: ReactNode) => <GuestLayout>{page}</GuestLayout>;

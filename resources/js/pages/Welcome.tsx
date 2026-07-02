import { Head } from '@inertiajs/react';
import { Landing } from '@/feature/landing/landing';

export default function Welcome() {
    return (
        <>
            <Head title="VHR — Controle de ponto sem planilha bagunçada" />
            <Landing />
        </>
    );
}

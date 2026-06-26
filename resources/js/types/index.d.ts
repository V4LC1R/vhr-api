export interface User {
    id: number;
    name: string;
    email: string;
}

export interface SharedProps {
    auth: { user: User | null };
    flash: { success?: string; error?: string };
}

declare module '@inertiajs/core' {
    interface PageProps extends SharedProps {}
}

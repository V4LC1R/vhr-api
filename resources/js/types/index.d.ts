// Identidade de login (sempre presente quando autenticado)
export interface AuthUser {
    id: string; // UUID
    email: string;
}

export interface Company {
    id: string; // UUID
    name: string;
}

// Contexto da empresa ativa — null quando nenhuma empresa foi selecionada
export interface AuthCurrent {
    companyId: string;
    company: Company | null;
    name: string | null; // person.name
    roles: string[];
    permissions: string[];
}

export interface SharedProps {
    auth: {
        user: AuthUser | null;
        current: AuthCurrent | null;
    };
}

declare module '@inertiajs/core' {
    interface PageProps extends SharedProps {}
}

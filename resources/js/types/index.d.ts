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

// Empresa que o user pode acessar — alimenta o seletor de empresa
export interface AuthCompany {
    companyId: string;
    name: string | null;
}

export interface SharedProps {
    auth: {
        user: AuthUser | null;
        current: AuthCurrent | null;
        companies: AuthCompany[];
    };
}

declare module '@inertiajs/core' {
    interface InertiaConfig {
        sharedPageProps: SharedProps;
    }
}

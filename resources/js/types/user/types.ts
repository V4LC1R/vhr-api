import type { UserCompany } from '@/types/userCompany/types';

/**
 * Resource (resposta da API) — UserResource.
 * `companies` só vem quando a relação userCompanies está carregada.
 */
export interface User {
    id: string;
    email: string;
    status: string; // sem enum no back ainda
    companies?: UserCompany[]; // when relationLoaded('userCompanies')
    createdAt: string | null;
    updatedAt: string | null;
}

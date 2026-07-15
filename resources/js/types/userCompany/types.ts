/**
 * Resource somente-leitura — UserCompanyResource.
 * Não há DTO de request no back, por isso esta pasta NÃO tem schemas.ts/mappers.ts.
 * `company` e `person` são versões parciais (só os campos escolhidos no resource)
 * e vêm apenas quando a relação está carregada (whenLoaded).
 */
export interface UserCompanyCompany {
    id: string;
    name: string;
    cnpj: string;
}

export interface UserCompanyPerson {
    id: string;
    name: string;
    email: string;
    cellphone: string;
}

export interface UserCompany {
    id: string;
    role?: string; // nome da role Spatie do usuário nessa empresa
    company?: UserCompanyCompany; // whenLoaded('company')
    person?: UserCompanyPerson; // whenLoaded('person')
    createdAt: string | null;
    updatedAt: string | null;
}

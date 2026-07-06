/**
 * Resource (resposta da API) — CompanyResource.
 * IDs são UUID; datas são ISO 8601.
 */
export interface Company {
    id: string;
    cnpj: string;
    name: string;
    createdAt: string | null;
    updatedAt: string | null;
}

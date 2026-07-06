/**
 * Resource (resposta da API) — PersonResource.
 */
export interface Person {
    id: string;
    name: string;
    email: string;
    cellphone: string;
    createdAt: string | null;
    updatedAt: string | null;
}

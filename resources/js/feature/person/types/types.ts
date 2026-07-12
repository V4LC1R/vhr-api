/**
 * Resource (resposta da API) — PersonResource.
 */
export interface Person {
    id: string;
    cpf: string | null;
    name: string;
    email: string;
    cellphone: string;
    pixKey: string | null;
    createdAt: string | null;
    updatedAt: string | null;
}

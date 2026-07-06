import type { Company } from './types';
import type { CompanyPayload } from './schemas';

/**
 * mapToForm: resource (resposta) -> valores do formulário.
 * mapToApi:  valores do formulário -> payload de request.
 */
export function mapToForm(resource: Company): CompanyPayload {
    return {
        name: resource.name,
        cnpj: resource.cnpj,
    };
}

export function mapToApi(form: CompanyPayload): CompanyPayload {
    return {
        name: form.name,
        cnpj: form.cnpj,
    };
}

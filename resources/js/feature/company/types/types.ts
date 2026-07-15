import type { Company } from '@/types/company/types';
import { PaginatedResponse, PaginateParams } from '@/types/paginate-requests';

export type { Company };

export type CompanyListFilters = {
    name?: string;
    cnpj?: string;
};

export type CompanyParamsPaginated = PaginateParams<CompanyListFilters>;
export type CompanyResponsePaginated = PaginatedResponse<Company>;

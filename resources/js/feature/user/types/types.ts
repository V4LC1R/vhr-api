import type { User } from '@/types/user/types';
import type { UserCompany } from '@/types/userCompany/types';
import { PaginatedResponse, PaginateParams } from '@/types/paginate-requests';

export type { User, UserCompany };

export type UserListFilters = {
    email?: string;
    status?: string;
};

export type UserParamsPaginated = PaginateParams<UserListFilters>;
export type UserResponsePaginated = PaginatedResponse<User>;

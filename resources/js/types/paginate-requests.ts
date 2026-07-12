export type PaginateParams<TFilters> = {
    filter?: TFilters;
    sort?: string;
    page?: number;
    per_page?: number;
};

export type PaginatedResponse<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};
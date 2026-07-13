<?php

namespace Modules\Job\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class EmploymentKindFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        // Spatie entrega array quando o filtro vem com vírgulas (kind=dayli,freelancer).
        $query->whereHas(
            'activeEmployment',
            fn ($q) => $q->whereIn('kind', (array) $value)
        );
    }
}

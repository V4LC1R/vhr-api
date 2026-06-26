<?php

namespace Modules\Job\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class EmploymentKindFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $query->whereHas(
            'activeEmployment',
            fn ($q) => $q->where('kind', $value)
        );
    }
}

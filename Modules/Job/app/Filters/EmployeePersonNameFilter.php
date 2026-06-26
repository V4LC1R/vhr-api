<?php

namespace Modules\Job\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class EmployeePersonNameFilter implements Filter
{
    public function __invoke(
        Builder $query,
        $value,
        string $property
    ): void {

        $query->whereHas(
            'person',
            fn ($person) => $person->where(
                'name',
                'ILIKE',
                "%{$value}%"
            )
        );
    }
}

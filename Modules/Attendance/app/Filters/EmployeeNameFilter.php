<?php

namespace Modules\Attendance\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class EmployeeNameFilter implements Filter
{
    public function __invoke(
        Builder $query,
        $value,
        string $property
    ): void {

        $query->whereHas(
            'employee.person',
            fn ($person) => $person->where(
                'name',
                'ILIKE',
                "%{$value}%"
            )
        );
    }
}

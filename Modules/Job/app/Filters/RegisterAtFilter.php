<?php

namespace Modules\Job\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class RegisterAtFilter implements Filter
{
    public function __invoke(
        Builder $query,
        $value,
        string $property
    ): void {

        $query->whereDate(
            'register_at',
            $value
        );
    }
}

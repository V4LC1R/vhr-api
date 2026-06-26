<?php

namespace Modules\Attendance\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Filtra os dias por mês no formato YYYY-MM (ex.: 2026-06).
 */
class MonthFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        [$year, $month] = array_pad(explode('-', (string) $value), 2, null);

        if (! $year || ! $month) {
            return;
        }

        $query->whereYear('date', $year)
            ->whereMonth('date', $month);
    }
}

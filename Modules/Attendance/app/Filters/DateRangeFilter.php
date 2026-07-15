<?php

namespace Modules\Attendance\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Filtra os dias por intervalo de datas, no formato "YYYY-MM-DD,YYYY-MM-DD".
 *
 * O QueryBuilder já quebra valores com vírgula em array antes de chamar o filtro
 * (`filter_value_splitting_enabled` no config), então `$value` chega como
 * `["YYYY-MM-DD", "YYYY-MM-DD"]` — só cai pra string se vier de outro lugar (ex.: testes
 * chamando o filtro diretamente).
 */
class DateRangeFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $parts = is_array($value) ? $value : explode(',', (string) $value);

        [$from, $to] = array_pad($parts, 2, null);

        if (! $from || ! $to) {
            return;
        }

        $query->whereBetween('date', [$from, $to]);
    }
}

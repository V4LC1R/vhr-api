<?php

namespace Modules\Attendance\Filters;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

/**
 * Filtra os dias pelo vínculo ATIVO do colaborador (clt, dayli, temporary,
 * freelancer). Aceita lista separada por vírgula.
 */
class EmploymentKindFilter implements Filter
{
    public function __invoke(
        Builder $query,
        $value,
        string $property
    ): void {

        $query->whereHas(
            'employee.activeEmployment',
            fn ($employment) => $employment->whereIn('kind', (array) $value)
        );
    }
}

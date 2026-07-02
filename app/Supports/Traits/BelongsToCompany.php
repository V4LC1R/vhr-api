<?php

declare(strict_types=1);

namespace App\Supports\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Escopa o model à empresa ativa (currentCompany()).
 *
 * - SELECT / UPDATE / DELETE: filtrados pelo global scope — qualquer query
 *   (inclusive route-model-binding por ID) só enxerga linhas da empresa ativa,
 *   então acesso a ID de outra empresa vira 404.
 * - INSERT: o companyId é preenchido automaticamente no creating.
 *
 * Quando não há empresa ativa (console, seeders, jobs, testes sem contexto),
 * o escopo é ignorado — o isolamento por request continua garantido pelo
 * middleware current.company + policies.
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $builder): void {
            $company = currentCompany();

            if ($company === null) {
                return;
            }

            $builder->where(
                $builder->getModel()->qualifyColumn('companyId'),
                $company->companyId
            );
        });

        static::creating(function (Model $model): void {
            $company = currentCompany();

            if ($company !== null && empty($model->companyId)) {
                $model->companyId = $company->companyId;
            }
        });
    }

    /**
     * Escape hatch p/ casos legítimos cross-company (relatórios admin,
     * rotinas de console). Use com consciência — quebra o isolamento.
     */
    public function scopeWithoutCompanyScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('company');
    }
}

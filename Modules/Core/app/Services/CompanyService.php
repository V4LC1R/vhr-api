<?php

namespace Modules\Core\Services;

use App\Exceptions\UniqueConstraintException;
use App\Helpers\DatabaseExceptionResolver;
use Illuminate\Database\QueryException;
use Modules\Core\Data\CompanyData;
use Modules\Core\Models\Company;

class CompanyService
{
    public function create(CompanyData $data)
    {
        try {
            return Company::create($data->toArray())
                ->toResource();
        } catch (QueryException $e) {
            if (DatabaseExceptionResolver::isUniqueViolation($e)) {
                throw new UniqueConstraintException(
                    'Já existe uma empresa cadastrada com os dados informados.',
                    previous: $e
                );
            }
            throw $e;
        }
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return Company::query()
            ->when(
                !empty($filters['name']),
                fn ($q) => $q->where('name', 'ilike', "%{$filters['name']}%")
            )
            ->when(
                !empty($filters['cnpj']),
                fn ($q) => $q->where('cnpj', 'like', "%{$filters['cnpj']}%") // Normalmente CNPJ usa like simples ou exato, mas mantive o padrão
            )
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($company) => $company->toResource());
    }

    public function update(Company $company, CompanyData $data): mixed
    {
        try {
            $company->update($data->toArray());

            return $company->fresh()->toResource();
        } catch (QueryException $e) {
            if (DatabaseExceptionResolver::isUniqueViolation($e)) {
                throw new UniqueConstraintException(
                    'Já existe uma empresa cadastrada com os dados informados.',
                    previous: $e
                );
            }
            throw $e;
        }
    }

    public function delete(Company $company): void
    {
        $company->delete();
    }
}

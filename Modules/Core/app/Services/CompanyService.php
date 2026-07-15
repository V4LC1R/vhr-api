<?php

namespace Modules\Core\Services;

use App\Exceptions\UniqueConstraintException;
use App\Helpers\DatabaseExceptionResolver;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\Core\Data\CompanyData;
use Modules\Core\Models\Company;
use Modules\Core\Models\UserCompany;
use Spatie\Permission\PermissionRegistrar;

class CompanyService
{
    /**
     * Cria a empresa e já vincula quem a criou como owner — sem isso a empresa
     * nasce inacessível (o seletor de empresas só lista as `userCompanies` do usuário).
     */
    public function create(CompanyData $data, string $userId, ?string $personId = null)
    {
        try {
            return DB::transaction(function () use ($data, $userId, $personId) {
                $company = Company::create($data->toArray());

                $userCompany = UserCompany::create([
                    'companyId' => $company->id,
                    'userId'    => $userId,
                    'personId'  => $personId,
                ]);

                app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
                $userCompany->syncRoles(['owner']);

                return $company->toResource();
            });
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

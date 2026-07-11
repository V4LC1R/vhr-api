<?php

namespace Modules\Core\Services;

use App\Exceptions\UniqueConstraintException;
use App\Helpers\DatabaseExceptionResolver;
use Illuminate\Database\QueryException;
use Modules\Core\Data\PersonData;
use Modules\Core\Models\Person;

class PersonService
{
    public function create(PersonData $data)
    {
        try {
            return Person::create($data->toArray())
                ->toResource();
        } catch (QueryException $e) {
            if (DatabaseExceptionResolver::isUniqueViolation($e)) {
                throw new UniqueConstraintException(
                    'Já existe uma pessoa cadastrada com os dados informados.',
                    previous: $e
                );
            }
            throw $e;
        }
    }

    public function findByCpf(string $cpf)
    {
        $person = Person::query()
            ->where('cpf', $cpf)
            ->first();

        return $person?->toResource();
    }

    public function list(array $filters = [], int $perPage = 15)
    {
        return Person::query()
            ->when(
                !empty($filters['name']),
                fn ($q) => $q->where('name', 'ilike', "%{$filters['name']}%")
            )
            ->when(
                !empty($filters['email']),
                fn ($q) => $q->where('email', 'ilike', "%{$filters['email']}%")
            )
            ->when(
                !empty($filters['cellphone']),
                fn ($q) => $q->where('cellphone', 'ilike', "%{$filters['cellphone']}%")
            )
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn ($person) => $person->toResource());
    }

    public function update(Person $person, PersonData $data): mixed
    {
        try {
            $person->update($data->toArray());

            return $person->fresh()->toResource();
        } catch (QueryException $e) {
            if (DatabaseExceptionResolver::isUniqueViolation($e)) {
                throw new UniqueConstraintException(
                    'Já existe uma pessoa cadastrada com os dados informados.',
                    previous: $e
                );
            }
            throw $e;
        }
    }

    public function delete(Person $person): void
    {
        $person->delete();
    }
}

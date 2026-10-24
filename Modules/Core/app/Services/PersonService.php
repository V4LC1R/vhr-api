<?php

namespace Modules\Core\Services;

use App\Exceptions\UniqueConstraintException;
use App\Helpers\DatabaseExceptionResolver;
use Illuminate\Database\QueryException;
use Modules\Core\Contracts\PersonServiceInterface;
use Modules\Core\Data\PersonData;
use Modules\Core\Exceptions\PersonUpdateException;
use Modules\Core\Models\Person;

class PersonService implements PersonServiceInterface
{
    public function create(PersonData $data): Person
    {
        try {
            return Person::create($data->toArray());
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
            ->with([
                'employees.employments' => fn ($query) => $query->latest('registerAt')->with('workload'),
            ])
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

    public function update(Person $person, PersonData $data): Person
    {
        try {
            $person->update($data->toArray());

            $person->fresh();

            return $person;
        } catch (QueryException $e) {
            if (DatabaseExceptionResolver::isUniqueViolation($e)) {
                throw new UniqueConstraintException(
                    'Já existe uma pessoa cadastrada com os dados informados.',
                    previous: $e
                );
            }
            throw new PersonUpdateException();
        }
    }

    public function delete(Person $person): void
    {
        $person->delete();
    }
}

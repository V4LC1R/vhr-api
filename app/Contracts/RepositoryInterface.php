<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface RepositoryInterface
{
    /** @return TModel|null */
    public function findById(string $id, array $relations = []): ?Model;

    /** @return TModel */
    public function create(array $data): Model;

    /** @return TModel|null */
    public function update(string $id, array $data): ?Model;

    public function delete(string $id): bool;
}

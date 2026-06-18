<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface CompanyRepositoryInterface
{
    public function getModel(): Model;

    public function findById(string $id, array $relations = []): ?Model;

    public function create(array $data): Model;

    public function update(string $id, array $data): Model;

    public function delete(string $id): bool;

    public function getModelClass(): string;
}

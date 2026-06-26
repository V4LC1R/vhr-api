<?php

declare(strict_types=1);

namespace App\Supports\Abstracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class BaseRepository
{
    /**
     * @param TModel $model
     */
    public function __construct(
        protected Model $model
    ) {
    }

    /**
     * @return TModel
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    public function getModelClass(): string
    {
        return $this->model::class;
    }

    /**
     * @return TModel|null
     */
    public function findById(string $id, array $relations = []): ?Model
    {
        return $this->model
            ->newQuery()
            ->with($relations)
            ->find($id);
    }

    /**
     * @return TModel
     */
    public function create(array $data): Model
    {
        return $this->model
            ->newQuery()
            ->create($data);
    }

    /**
     * @return TModel|null
     */
    public function update(string $id, array $data): ?Model
    {
        $model = $this->findById($id);

        if (! $model) {
            return null;
        }

        $model->update($data);

        return $model->fresh();
    }

    public function delete(string $id): bool
    {
        $model = $this->findById($id);

        if (! $model) {
            return false;
        }

        return (bool) $model->delete();
    }
}

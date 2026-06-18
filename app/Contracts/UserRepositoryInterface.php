<?php

declare(strict_types=1);

namespace App\Contracts;

interface UserRepositoryInterface
{
    public function getModel();

    public function findById(string $id, array $relations = []);

    public function findByEmail(string $email);

    public function create(array $data);

    public function update(string $id, array $data);

    public function delete(string $id): bool;

    public function getModelClass(): string;
}

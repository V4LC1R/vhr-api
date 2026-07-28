<?php

declare(strict_types=1);

namespace App\Contracts;

interface PersonServiceInterface
{
    public function create(array $data);
}

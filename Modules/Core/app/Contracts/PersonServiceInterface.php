<?php

declare(strict_types=1);

namespace Modules\Core\Contracts;

interface PersonServiceInterface
{
    public function create(array $data);
}

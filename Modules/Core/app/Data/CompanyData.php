<?php

namespace Modules\Core\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class CompanyData extends Data
{
    public function __construct(
        public ?string $id,
        public string|Optional $name,
        public string $cnpj,
    ) {
    }
}

<?php

namespace Modules\Core\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Modules\Core\Models\Company;

class CompanyData extends Data
{
    public function __construct(
        public ?string $id,
        public string|Optional $name,
        public string|Optional $cnpj,
    ) {
    }


    public static function fromModel(Company $company): self
    {
        return new self(
            id: $company->id,
            name: $company->name,
            cnpj: $company->cnpj,
        );
    }
}

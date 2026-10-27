<?php

declare(strict_types=1);

namespace Modules\Job\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Optional;

class EmployeeData extends Data
{
    public function __construct(
        #[Uuid]
        public string|Optional $companyId,
        public array $person,
        public EmploymentData $employment,
    ) {
    }
}

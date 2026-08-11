<?php

declare(strict_types=1);

namespace Modules\Job\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateEmployeeData extends Data
{
    public function __construct(
        public EmploymentChangesData|Optional $employment,
        public array|Optional $person,
    ) {
    }
}

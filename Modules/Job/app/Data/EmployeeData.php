<?php

declare(strict_types=1);

namespace Modules\Job\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Optional;

class EmployeeData extends Data
{
    public function __construct(
        #[Uuid]
        public string|Optional $companyId,
        #[Uuid]
        public string|Optional $personId,
        public array|Optional $person,
        #[Uuid]
        public string|Optional $workloadId,
        public string|Optional $kind,
        public string|Optional $status,
        public bool|Optional $isProbationary,
    ) {
    }
}

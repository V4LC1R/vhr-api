<?php

declare(strict_types=1);

namespace Modules\Job\Data;

use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class EmploymentData extends Data
{
    public function __construct(
        #[Uuid]
        public string|Optional $id,
        #[Uuid]
        public string|Optional $employeeId,
        #[Uuid]
        public string|Optional $workloadId,
        public string|Optional $kind,
        public string|Optional $status,
        public bool|Optional $isProbationary,
    ) {
    }
}

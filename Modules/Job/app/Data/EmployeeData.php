<?php

declare(strict_types=1);

namespace Modules\Job\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Optional;

class EmployeeData extends Data
{
    public function __construct(
        #[ Uuid]
        public string|Optional $companyId,
        #[Uuid]
        public string|Optional $personId,
        #[Required, Uuid]
        public string $workloadId,
        public string|Optional $status = 'hired'
    ) {
    }
}

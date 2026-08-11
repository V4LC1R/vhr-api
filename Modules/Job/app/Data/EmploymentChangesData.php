<?php

declare(strict_types=1);

namespace Modules\Job\Data;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class EmploymentChangesData extends Data
{
    public function __construct(
        #[DataCollectionOf(EmploymentData::class)]
        public array|Optional $add,
        #[DataCollectionOf(EmploymentData::class)]
        public array|Optional $update,
        public array|Optional $remove,
    ) {
    }
}

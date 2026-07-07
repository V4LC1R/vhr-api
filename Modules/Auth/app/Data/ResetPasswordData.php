<?php

namespace Modules\Auth\Data;

use Spatie\LaravelData\Data;

class ResetPasswordData extends Data
{
    public function __construct(
        public string $token,
        public string $password,
    ) {
    }
}

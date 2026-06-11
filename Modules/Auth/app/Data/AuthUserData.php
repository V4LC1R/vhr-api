<?php

namespace Modules\Auth\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Attributes\Validation\Email;

class AuthUserData extends Data
{
    public function __construct(
        // No Update, se o usuário não enviar o nome, ele vira uma instância de Optional
        public string $password,
        #[Email]
        public string|Optional $email
    ) {
    }
}

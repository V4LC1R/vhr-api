<?php

namespace Modules\Core\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Attributes\Validation\Email;

class PersonData extends Data
{
    public function __construct(
        // No Create ele não vem, no Update ele vem.
        public ?string $id,
        public string|Optional $cpf,
        // No Update, se o usuário não enviar o nome, ele vira uma instância de Optional
        public string|Optional $name,
        #[Email]
        public string|Optional $email,
        public string|Optional $cellphone,
    ) {
    }
}

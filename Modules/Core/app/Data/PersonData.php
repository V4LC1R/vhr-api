<?php

namespace Modules\Core\Data;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

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
        public string|null|Optional $pixKey,
        #[WithCast(
            DateTimeInterfaceCast::class,
            format: [
                'Y-m-d\TH:i:sP',
                'Y-m-dH:i:sP',
                'Y-m-d\TH:i:s.v\Z',
            ]
        ),
        ]
        public Carbon $birthdate
    ) {
    }
}

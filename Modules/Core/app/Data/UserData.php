<?php

namespace Modules\Core\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Modules\Core\Models\User;

class UserData extends Data
{
    public function __construct(
        public ?string $id,
        public string|Optional $email,
        public string|Optional $password,
        public string|Optional $status,
    ) {
    }


    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            email: $user->email,
            password: $user->password,
            status: $user->status,
        );
    }
}

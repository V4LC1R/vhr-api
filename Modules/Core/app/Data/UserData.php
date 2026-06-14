<?php

namespace Modules\Core\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Modules\Core\Models\User;

class UserData extends Data
{
    public function __construct(
        public ?string $id,
        public string|Optional $username,
        public string|Optional $email,
        public string|Optional $password,
        public string|Optional $status,
        public string|Optional $personId,
        public PersonData|Optional|null $person,
    ) {
    }

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            password: $user->password,
            status: $user->status,
            personId: $user->personId,
            person: $user->relationLoaded('person') && $user->person
                ? PersonData::fromModel($user->person)
                : null
        );
    }
}

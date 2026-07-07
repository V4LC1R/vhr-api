<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Enums\TokenPasswordStatusEnum;
use Modules\Core\Models\PasswordResetToken;
use Modules\Core\Models\User;

#[UseModel(PasswordResetToken::class)]
class PasswordResetTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token'       => hash('sha256', fake()->unique()->uuid()),
            'status'      => TokenPasswordStatusEnum::PENDING,
            'userId'      => User::factory(),
            'ipAddress'   => fake()->ipv4(),
            'userAgent'   => fake()->userAgent(),
            'expiresAt'   => now()->addHour(),
            'requestedAt' => now(),
            'usedAt'      => null,
        ];
    }

    /**
     * Token já utilizado.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TokenPasswordStatusEnum::USED,
            'usedAt' => now(),
        ]);
    }

    /**
     * Token expirado.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expiresAt' => now()->subHour(),
        ]);
    }
}

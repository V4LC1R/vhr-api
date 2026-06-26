<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\User;

#[UseModel(User::class)]
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email'     => fake()->unique()->safeEmail(),
            // Como o model usa cast 'hashed', passamos apenas o texto puro
            'password'  => 'password',
            'status'    => fake()->randomElement(['active', 'inactive']),
        ];
    }
}

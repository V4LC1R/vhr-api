<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Person;

#[UseModel(Person::class)]
class PersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cpf'       => fake('pt_BR')->unique()->cpf(false),
            'name'      => fake()->name(),
            'email'     => fake()->unique()->safeEmail(),
            'cellphone' => fake()->phoneNumber(),
        ];
    }
}

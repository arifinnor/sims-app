<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasEmail = fake()->boolean(70);

        return [
            'name' => fake()->name(),
            'email' => $hasEmail ? fake()->unique()->safeEmail() : null,
            'phone' => fake()->optional(0.7)->phoneNumber(),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guardian>
 */
class GuardianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasEmail = fake()->boolean(80);

        return [
            'name' => fake()->name(),
            'email' => $hasEmail ? fake()->unique()->safeEmail() : null,
            'phone' => fake()->optional(0.8)->phoneNumber(),
            'relationship' => fake()->optional(0.6)->randomElement(['Mother', 'Father', 'Legal Guardian', 'Grandmother', 'Grandfather', 'Uncle', 'Aunt', 'Other']),
            'address' => fake()->optional(0.5)->address(),
        ];
    }
}

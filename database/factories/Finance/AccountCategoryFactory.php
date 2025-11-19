<?php

namespace Database\Factories\Finance;

use App\Enums\Finance\ReportType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Finance\AccountCategory>
 */
class AccountCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'report_type' => fake()->randomElement(ReportType::values()),
            'sequence' => fake()->unique()->numberBetween(0, 999),
        ];
    }
}

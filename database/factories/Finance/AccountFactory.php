<?php

namespace Database\Factories\Finance;

use App\Enums\Finance\AccountType;
use App\Models\Finance\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Finance\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(AccountType::cases());

        $categories = match ($type) {
            AccountType::Asset => ['current_asset', 'fixed_asset', 'intangible_asset'],
            AccountType::Liability => ['current_liability', 'long_term_liability'],
            AccountType::Equity => ['capital', 'retained_earnings'],
            AccountType::Revenue => ['operating_revenue', 'non_operating_revenue'],
            AccountType::Expense => ['operating_expense', 'non_operating_expense', 'cost_of_goods_sold'],
        };

        $balance = 0;

        return [
            'account_number' => fake()->unique()->numerify('####'),
            'name' => fake()->words(2, true),
            'type' => $type,
            'category' => fake()->randomElement($categories),
            'parent_account_id' => null,
            'balance' => $balance,
            'currency' => 'IDR',
            'status' => 'active',
            'description' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the account should have a parent.
     */
    public function withParent(?Account $parent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_account_id' => $parent?->id ?? Account::factory(),
        ]);
    }

    /**
     * Create an asset account.
     */
    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AccountType::Asset,
            'category' => fake()->randomElement(['current_asset', 'fixed_asset', 'intangible_asset']),
        ]);
    }

    /**
     * Create a liability account.
     */
    public function liability(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AccountType::Liability,
            'category' => fake()->randomElement(['current_liability', 'long_term_liability']),
        ]);
    }

    /**
     * Create an equity account.
     */
    public function equity(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AccountType::Equity,
            'category' => fake()->randomElement(['capital', 'retained_earnings']),
        ]);
    }

    /**
     * Create a revenue account.
     */
    public function revenue(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AccountType::Revenue,
            'category' => fake()->randomElement(['operating_revenue', 'non_operating_revenue']),
        ]);
    }

    /**
     * Create an expense account.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AccountType::Expense,
            'category' => fake()->randomElement(['operating_expense', 'non_operating_expense', 'cost_of_goods_sold']),
        ]);
    }
}

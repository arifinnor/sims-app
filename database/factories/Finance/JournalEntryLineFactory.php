<?php

namespace Database\Factories\Finance;

use App\Enums\Finance\EntryDirection;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\JournalEntryLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Finance\JournalEntryLine>
 */
class JournalEntryLineFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Finance\JournalEntryLine>
     */
    protected $model = JournalEntryLine::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'journal_entry_id' => JournalEntry::factory(),
            'chart_of_account_id' => ChartOfAccount::factory(),
            'direction' => fake()->randomElement([EntryDirection::Debit->value, EntryDirection::Credit->value]),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'description' => fake()->optional()->words(3, true),
        ];
    }

    public function debit(): self
    {
        return $this->state(fn (): array => [
            'direction' => EntryDirection::Debit->value,
        ]);
    }

    public function credit(): self
    {
        return $this->state(fn (): array => [
            'direction' => EntryDirection::Credit->value,
        ]);
    }

    public function forJournal(JournalEntry $journal): self
    {
        return $this->state(fn (): array => [
            'journal_entry_id' => $journal->id,
        ]);
    }

    public function forAccount(ChartOfAccount $account): self
    {
        return $this->state(fn (): array => [
            'chart_of_account_id' => $account->id,
        ]);
    }
}

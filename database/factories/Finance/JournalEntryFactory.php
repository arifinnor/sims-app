<?php

namespace Database\Factories\Finance;

use App\Enums\Finance\JournalStatus;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\TransactionType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Finance\JournalEntry>
 */
class JournalEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\Finance\JournalEntry>
     */
    protected $model = JournalEntry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $transactionDate = fake()->dateTimeBetween('-1 year', 'now');
        $dateString = $transactionDate->format('Ymd');

        return [
            'transaction_type_id' => TransactionType::factory(),
            'transaction_date' => $transactionDate,
            'reference_number' => JournalEntry::generateReference($dateString),
            'description' => fake()->optional()->sentence(),
            'status' => JournalStatus::Posted->value,
            'total_amount' => fake()->randomFloat(2, 100, 10000),
            'student_id' => null,
            'created_by' => null,
        ];
    }

    public function draft(): self
    {
        return $this->state(fn (): array => [
            'status' => JournalStatus::Draft->value,
        ]);
    }

    public function void(): self
    {
        return $this->state(fn (): array => [
            'status' => JournalStatus::Void->value,
        ]);
    }

    public function forStudent(Student $student): self
    {
        return $this->state(fn (): array => [
            'student_id' => $student->id,
        ]);
    }

    public function withCreator(User $user): self
    {
        return $this->state(fn (): array => [
            'created_by' => $user->id,
        ]);
    }
}

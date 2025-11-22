<?php

namespace App\Services\Finance;

use App\Enums\Finance\EntryDirection;
use App\Enums\Finance\JournalStatus;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\JournalEntryLine;
use App\Models\Finance\TransactionType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalEntryService
{
    /**
     * Create a new journal entry with auto-balancing.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     * @throws ModelNotFoundException
     */
    public function create(string $typeCode, array $data): JournalEntry
    {
        $type = TransactionType::query()
            ->where('code', $typeCode)
            ->where('is_active', true)
            ->with(['accounts.chartOfAccount'])
            ->firstOrFail();

        $lines = [];
        $totalDebit = 0;
        $totalCredit = 0;

        // Process fixed legs from transaction type configuration
        foreach ($type->accounts as $account) {
            if ($account->chart_of_account_id === null) {
                continue;
            }

            $lineAmount = (float) $data['amount'];
            $direction = $account->direction;

            $lines[] = [
                'chart_of_account_id' => $account->chart_of_account_id,
                'direction' => $direction,
                'amount' => $lineAmount,
                'description' => $data['description'] ?? null,
            ];

            if ($direction === EntryDirection::Debit) {
                $totalDebit += $lineAmount;
            } else {
                $totalCredit += $lineAmount;
            }
        }

        // Auto-balancing algorithm
        $difference = abs($totalDebit - $totalCredit);

        if ($difference > 0) {
            // Imbalance detected - need dynamic leg (plug)
            if (empty($data['cash_account_id'])) {
                throw ValidationException::withMessages([
                    'cash_account_id' => ['Cash Account is required when transaction is not balanced.'],
                ]);
            }

            // Determine direction: opposite of heavier side
            $dynamicDirection = $totalDebit > $totalCredit
                ? EntryDirection::Credit
                : EntryDirection::Debit;

            $lines[] = [
                'chart_of_account_id' => $data['cash_account_id'],
                'direction' => $dynamicDirection,
                'amount' => $difference,
                'description' => $data['description'] ?? null,
            ];
        }

        // Generate reference number
        $referenceNumber = $data['external_ref'] ?? null;
        if ($referenceNumber === null || $referenceNumber === '') {
            $referenceNumber = JournalEntry::generateReference($data['date']);
        } else {
            // Validate uniqueness if external_ref is provided
            if (JournalEntry::query()->where('reference_number', $referenceNumber)->exists()) {
                throw ValidationException::withMessages([
                    'external_ref' => ['Reference number already exists.'],
                ]);
            }
        }

        return DB::transaction(function () use ($type, $data, $lines, $referenceNumber): JournalEntry {
            $journal = JournalEntry::create([
                'transaction_type_id' => $type->id,
                'transaction_date' => $data['date'],
                'reference_number' => $referenceNumber,
                'description' => $data['description'] ?? null,
                'status' => JournalStatus::Posted,
                'total_amount' => $data['amount'],
                'student_id' => $data['student_id'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($lines as $lineData) {
                JournalEntryLine::create([
                    'journal_entry_id' => $journal->id,
                    ...$lineData,
                ]);
            }

            return $journal->load('lines');
        });
    }

    /**
     * Void a journal entry.
     *
     * @throws \Exception
     */
    public function void(string $journalId): JournalEntry
    {
        $journal = JournalEntry::query()->findOrFail($journalId);

        if ($journal->status === JournalStatus::Void) {
            throw new \Exception('Journal entry is already voided.');
        }

        if ($journal->status !== JournalStatus::Posted) {
            throw new \Exception('Only Posted journal entries can be voided.');
        }

        $journal->status = JournalStatus::Void;
        $journal->save();

        return $journal;
    }
}

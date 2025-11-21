<?php

namespace App\Services\Finance;

use App\Enums\Finance\JournalStatus;
use App\Models\Finance\JournalEntry;

class JournalEntryService
{
    /**
     * Void a journal entry.
     *
     * @throws \Exception
     */
    public function void(JournalEntry $journal): JournalEntry
    {
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

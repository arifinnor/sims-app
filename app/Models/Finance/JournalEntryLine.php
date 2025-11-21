<?php

namespace App\Models\Finance;

use App\Enums\Finance\EntryDirection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'journal_entry_id',
        'chart_of_account_id',
        'direction',
        'amount',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'direction' => EntryDirection::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ChartOfAccount, JournalEntryLine>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }

    /**
     * @return BelongsTo<JournalEntry, JournalEntryLine>
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * Get the debit amount (returns amount if direction is Debit, else 0).
     */
    public function getDebitAttribute(): string
    {
        return $this->direction === EntryDirection::Debit
            ? $this->amount
            : '0.00';
    }

    /**
     * Get the credit amount (returns amount if direction is Credit, else 0).
     */
    public function getCreditAttribute(): string
    {
        return $this->direction === EntryDirection::Credit
            ? $this->amount
            : '0.00';
    }
}

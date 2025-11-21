<?php

namespace App\Models\Finance;

use App\Enums\Finance\JournalStatus;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
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
        'transaction_type_id',
        'transaction_date',
        'reference_number',
        'description',
        'status',
        'total_amount',
        'student_id',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'status' => JournalStatus::class,
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TransactionType, JournalEntry>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class, 'transaction_type_id');
    }

    /**
     * @return HasMany<JournalEntryLine>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }

    /**
     * @return BelongsTo<Student, JournalEntry>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * @return BelongsTo<User, JournalEntry>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  Builder<JournalEntry>  $query
     * @return Builder<JournalEntry>
     */
    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', JournalStatus::Posted);
    }

    /**
     * @param  Builder<JournalEntry>  $query
     * @return Builder<JournalEntry>
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', JournalStatus::Draft);
    }

    /**
     * @param  Builder<JournalEntry>  $query
     * @return Builder<JournalEntry>
     */
    public function scopeVoid(Builder $query): Builder
    {
        return $query->where('status', JournalStatus::Void);
    }

    /**
     * Prevent deletion of journal entries (Immutable Ledger Principle).
     */
    public function delete(): bool
    {
        throw new \Exception('Strict Ledger: Journal Entries cannot be deleted. Use void() instead.');
    }

    /**
     * Void a journal entry (only Posted entries can be voided).
     */
    public function void(): bool
    {
        if ($this->status === JournalStatus::Void) {
            throw new \Exception('Journal entry is already voided.');
        }

        if ($this->status !== JournalStatus::Posted) {
            throw new \Exception('Only Posted journal entries can be voided.');
        }

        $this->status = JournalStatus::Void;

        return $this->save();
    }

    /**
     * Generate a unique reference number in format: TRX-{Ymd}-{sequence}.
     */
    public static function generateReference(?string $date = null): string
    {
        $date = $date ?? now()->format('Ymd');
        $prefix = "TRX-{$date}-";

        $lastReference = static::query()
            ->where('reference_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('reference_number')
            ->value('reference_number');

        if ($lastReference) {
            $lastSequence = (int) substr($lastReference, -4);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix.str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }
}

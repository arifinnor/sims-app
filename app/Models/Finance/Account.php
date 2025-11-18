<?php

namespace App\Models\Finance;

use App\Enums\Finance\AccountType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    /** @use HasFactory<\Database\Factories\Finance\AccountFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'account_number',
        'name',
        'type',
        'category',
        'parent_account_id',
        'balance',
        'currency',
        'status',
        'description',
    ];

    /**
     * Get the parent account.
     *
     * @return BelongsTo<Account, Account>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    /**
     * Get the child accounts.
     *
     * @return HasMany<Account>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    /**
     * Get all descendant accounts recursively.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Account>
     */
    public function descendants()
    {
        $descendants = $this->children()->get();

        foreach ($this->children()->get() as $child) {
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }

    /**
     * Get all ancestor accounts up to the root.
     *
     * @return \Illuminate\Support\Collection<int, Account>
     */
    public function ancestors()
    {
        $ancestors = collect();

        if (! $this->relationLoaded('parent')) {
            return $ancestors;
        }

        $current = $this->parent;
        while ($current !== null) {
            $ancestors->push($current);
            if (! $current->relationLoaded('parent')) {
                break;
            }
            $current = $current->parent;
        }

        return $ancestors->reverse();
    }

    /**
     * Check if account is an asset.
     */
    public function isAsset(): bool
    {
        return $this->type === AccountType::Asset;
    }

    /**
     * Check if account is a liability.
     */
    public function isLiability(): bool
    {
        return $this->type === AccountType::Liability;
    }

    /**
     * Check if account is equity.
     */
    public function isEquity(): bool
    {
        return $this->type === AccountType::Equity;
    }

    /**
     * Check if account is revenue.
     */
    public function isRevenue(): bool
    {
        return $this->type === AccountType::Revenue;
    }

    /**
     * Check if account is expense.
     */
    public function isExpense(): bool
    {
        return $this->type === AccountType::Expense;
    }

    /**
     * Check if account has children.
     */
    public function isParent(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if account is a leaf (no children).
     */
    public function isLeaf(): bool
    {
        return ! $this->isParent();
    }

    /**
     * Get the full hierarchical account number.
     */
    public function getFullAccountNumber(): string
    {
        // If parent is not loaded, just return the account number
        if (! $this->relationLoaded('parent') || $this->parent === null) {
            return $this->account_number;
        }

        // Build the chain from what we have loaded
        $numbers = [];
        $current = $this;

        // Walk up the chain using loaded relationships
        while ($current !== null) {
            array_unshift($numbers, $current->account_number);

            if ($current->relationLoaded('parent') && $current->parent !== null) {
                $current = $current->parent;
            } else {
                break;
            }
        }

        return implode('.', $numbers);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AccountType::class,
            'balance' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}

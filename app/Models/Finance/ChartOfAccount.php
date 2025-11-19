<?php

namespace App\Models\Finance;

use App\Enums\Finance\AccountType;
use App\Enums\Finance\NormalBalance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChartOfAccount extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

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
        'category_id',
        'code',
        'name',
        'description',
        'parent_id',
        'account_type',
        'normal_balance',
        'is_posting',
        'is_cash',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category_id' => 'string',
            'parent_id' => 'string',
            'account_type' => AccountType::class,
            'normal_balance' => NormalBalance::class,
            'is_posting' => 'boolean',
            'is_cash' => 'boolean',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AccountCategory, ChartOfAccount>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AccountCategory::class, 'category_id');
    }

    /**
     * @return BelongsTo<ChartOfAccount, ChartOfAccount>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<ChartOfAccount>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @param  Builder<ChartOfAccount>  $query
     * @return Builder<ChartOfAccount>
     */
    public function scopePosting(Builder $query): Builder
    {
        return $query->where('is_posting', true);
    }

    /**
     * @param  Builder<ChartOfAccount>  $query
     * @return Builder<ChartOfAccount>
     */
    public function scopeHeader(Builder $query): Builder
    {
        return $query->where('is_posting', false);
    }

    /**
     * @param  Builder<ChartOfAccount>  $query
     * @return Builder<ChartOfAccount>
     */
    public function scopeCash(Builder $query): Builder
    {
        return $query->where('is_cash', true);
    }

    /**
     * @param  Builder<ChartOfAccount>  $query
     * @return Builder<ChartOfAccount>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

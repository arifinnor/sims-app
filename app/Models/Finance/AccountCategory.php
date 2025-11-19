<?php

namespace App\Models\Finance;

use App\Enums\Finance\ReportType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountCategory extends Model
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
        'name',
        'report_type',
        'sequence',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('sequence', function (Builder $builder): void {
            $builder->orderBy('sequence');
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'report_type' => ReportType::class,
            'sequence' => 'integer',
        ];
    }

    /**
     * @return HasMany<ChartOfAccount>
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'category_id');
    }
}

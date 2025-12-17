<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    /** @use HasFactory<\Database\Factories\AcademicYearFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::updating(function (AcademicYear $academicYear): void {
            if ($academicYear->isDirty('is_active') && $academicYear->is_active) {
                static::where('id', '!=', $academicYear->id)
                    ->update(['is_active' => false]);
            }
        });

        static::creating(function (AcademicYear $academicYear): void {
            if ($academicYear->is_active) {
                static::where('id', '!=', $academicYear->id ?? '')
                    ->update(['is_active' => false]);
            }
        });
    }

    /**
     * Scope a query to only include the current active academic year.
     *
     * @param  \Illuminate\Database\Eloquent\Builder<AcademicYear>  $query
     * @return \Illuminate\Database\Eloquent\Builder<AcademicYear>
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the classrooms for this academic year.
     *
     * @return HasMany<Classroom>
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}

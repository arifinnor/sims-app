<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'student_number',
        'name',
        'email',
        'phone',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Student $student): void {
            if (empty($student->student_number)) {
                $student->student_number = static::generateUniqueStudentNumber();
            }
        });
    }

    /**
     * Generate a unique student number.
     */
    protected static function generateUniqueStudentNumber(): string
    {
        $maxAttempts = 100;
        $attempt = 0;
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        do {
            $randomPart = '';
            for ($i = 0; $i < 5; $i++) {
                $randomPart .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $number = 'STU-'.$randomPart;
            $exists = static::withTrashed()->where('student_number', $number)->exists();
            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        if ($exists) {
            throw new \RuntimeException('Unable to generate unique student number after '.$maxAttempts.' attempts.');
        }

        return $number;
    }

    /**
     * Get the guardians that belong to this student.
     *
     * @return BelongsToMany<Guardian>
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot(['relationship_type', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}

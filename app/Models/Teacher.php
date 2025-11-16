<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    /** @use HasFactory<\Database\Factories\TeacherFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'teacher_number',
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

        static::creating(function (Teacher $teacher): void {
            if (empty($teacher->teacher_number)) {
                $teacher->teacher_number = static::generateUniqueTeacherNumber();
            }
        });
    }

    /**
     * Generate a unique teacher number.
     */
    protected static function generateUniqueTeacherNumber(): string
    {
        $maxAttempts = 100;
        $attempt = 0;
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        do {
            $randomPart = '';
            for ($i = 0; $i < 5; $i++) {
                $randomPart .= $characters[random_int(0, strlen($characters) - 1)];
            }
            $number = 'TCH-'.$randomPart;
            $exists = static::withTrashed()->where('teacher_number', $number)->exists();
            $attempt++;
        } while ($exists && $attempt < $maxAttempts);

        if ($exists) {
            throw new \RuntimeException('Unable to generate unique teacher number after '.$maxAttempts.' attempts.');
        }

        return $number;
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

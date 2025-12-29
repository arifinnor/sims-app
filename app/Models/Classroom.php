<?php

namespace App\Models;

use App\Enums\Academic\EnrollmentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends Model
{
    /** @use HasFactory<\Database\Factories\ClassroomFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'academic_year_id',
        'homeroom_teacher_id',
        'grade_level',
        'name',
        'capacity',
    ];

    /**
     * Get the academic year that owns this classroom.
     *
     * @return BelongsTo<AcademicYear, Classroom>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the homeroom teacher for this classroom.
     *
     * @return BelongsTo<Teacher, Classroom>
     */
    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    /**
     * Get the students enrolled in this classroom.
     *
     * @return BelongsToMany<Student>
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'class_students')
            ->withPivot(['status'])
            ->withTimestamps()
            ->using(ClassStudent::class);
    }

    /**
     * Get only active students enrolled in this classroom.
     *
     * @return BelongsToMany<Student>
     */
    public function activeStudents(): BelongsToMany
    {
        return $this->students()
            ->wherePivot('status', EnrollmentStatus::Active->value);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'capacity' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}

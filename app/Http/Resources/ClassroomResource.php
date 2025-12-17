<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassroomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'academicYearId' => $this->academic_year_id,
            'academic_year_id' => $this->academic_year_id,
            'homeroomTeacherId' => $this->homeroom_teacher_id,
            'homeroom_teacher_id' => $this->homeroom_teacher_id,
            'gradeLevel' => $this->grade_level,
            'grade_level' => $this->grade_level,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'createdAt' => $this->created_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
            'academicYear' => $this->whenLoaded('academicYear', function () {
                return AcademicYearResource::make($this->academicYear)->resolve();
            }),
            'homeroomTeacher' => $this->whenLoaded('homeroomTeacher', function () {
                return $this->homeroomTeacher ? [
                    'id' => $this->homeroomTeacher->id,
                    'teacherNumber' => $this->homeroomTeacher->teacher_number,
                    'name' => $this->homeroomTeacher->name,
                    'email' => $this->homeroomTeacher->email,
                ] : null;
            }),
            'students' => $this->whenLoaded('students', function () {
                return $this->students->map(fn ($student) => [
                    'id' => $student->id,
                    'studentNumber' => $student->student_number,
                    'name' => $student->name,
                    'email' => $student->email,
                    'pivot' => [
                        'status' => $student->pivot->status->value ?? $student->pivot->status,
                    ],
                ]);
            }),
            'activeStudentsCount' => $this->whenLoaded('students', function () {
                return $this->students->where('pivot.status', \App\Enums\Academic\EnrollmentStatus::Active->value)->count();
            }),
        ];
    }
}

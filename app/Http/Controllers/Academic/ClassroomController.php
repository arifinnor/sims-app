<?php

namespace App\Http\Controllers\Academic;

use App\Enums\Academic\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\ClassroomEnrollStudentsRequest;
use App\Http\Requests\Academic\ClassroomIndexRequest;
use App\Http\Requests\Academic\ClassroomStoreRequest;
use App\Http\Requests\Academic\ClassroomUpdateRequest;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the classrooms.
     */
    public function index(ClassroomIndexRequest $request): Response
    {
        $validated = $request->validated();

        $classrooms = Classroom::query()
            ->with(['academicYear', 'homeroomTeacher'])
            ->when($validated['with_trashed'] === 'only', fn ($query) => $query->onlyTrashed())
            ->when($validated['with_trashed'] === 'all', fn ($query) => $query->withTrashed())
            ->when($validated['academic_year_id'], fn ($query, $academicYearId) => $query->where('academic_year_id', $academicYearId))
            ->when($validated['grade_level'], fn ($query, $gradeLevel) => $query->where('grade_level', $gradeLevel))
            ->when($validated['search'], function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('homeroomTeacher', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('grade_level')
            ->orderBy('name')
            ->cursorPaginate($validated['per_page'])
            ->withQueryString()
            ->through(fn (Classroom $classroom) => ClassroomResource::make($classroom)->resolve());

        return Inertia::render('Academic/Classrooms/Index', [
            'classrooms' => $classrooms,
            'perPageOptions' => config('pagination.per_page_options'),
        ]);
    }

    /**
     * Show the form for creating a new classroom.
     */
    public function create(): Response
    {
        $academicYears = \App\Models\AcademicYear::query()
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn ($year) => [
                'id' => $year->id,
                'name' => $year->name,
            ]);

        $teachers = \App\Models\Teacher::query()
            ->orderBy('name')
            ->get()
            ->map(fn ($teacher) => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'teacherNumber' => $teacher->teacher_number,
            ]);

        return Inertia::render('Academic/Classrooms/Create', [
            'academicYears' => $academicYears,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Store a newly created classroom in storage.
     */
    public function store(ClassroomStoreRequest $request): RedirectResponse
    {
        $classroom = Classroom::create($request->validated());

        return to_route('classrooms.edit', $classroom)->with('success', 'Classroom created.');
    }

    /**
     * Display the specified classroom.
     */
    public function show(Classroom $classroom): Response
    {
        $classroom->load(['academicYear', 'homeroomTeacher', 'students']);

        return Inertia::render('Academic/Classrooms/Show', [
            'classroom' => ClassroomResource::make($classroom)->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified classroom.
     */
    public function edit(Classroom $classroom): Response
    {
        $classroom->load(['academicYear', 'homeroomTeacher', 'students']);

        return Inertia::render('Academic/Classrooms/Edit', [
            'classroom' => ClassroomResource::make($classroom)->resolve(),
        ]);
    }

    /**
     * Update the specified classroom in storage.
     */
    public function update(ClassroomUpdateRequest $request, Classroom $classroom): RedirectResponse
    {
        $classroom->update($request->validated());

        return to_route('classrooms.edit', $classroom)->with('success', 'Classroom updated.');
    }

    /**
     * Remove the specified classroom from storage.
     */
    public function destroy(Classroom $classroom): RedirectResponse
    {
        $classroom->delete();

        return to_route('classrooms.index')->with('success', 'Classroom deleted.');
    }

    /**
     * Restore the specified soft-deleted classroom.
     */
    public function restore(string $classroom): RedirectResponse
    {
        $classroom = Classroom::withTrashed()->findOrFail($classroom);
        $classroom->restore();

        return to_route('classrooms.index')->with('success', 'Classroom restored.');
    }

    /**
     * Permanently delete the specified classroom.
     */
    public function forceDelete(string $classroom): RedirectResponse
    {
        $classroom = Classroom::withTrashed()->findOrFail($classroom);
        $classroom->forceDelete();

        return to_route('classrooms.index')->with('success', 'Classroom permanently deleted.');
    }

    /**
     * Show the enrollment page for a classroom.
     */
    public function enrollment(Classroom $classroom): Response
    {
        $classroom->load(['academicYear', 'homeroomTeacher', 'students']);

        // Get unassigned students (students not enrolled in any active classroom in the same academic year)
        $enrolledStudentIds = DB::table('class_students')
            ->join('classrooms', 'class_students.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.academic_year_id', $classroom->academic_year_id)
            ->where('class_students.status', EnrollmentStatus::Active->value)
            ->where('class_students.student_id', '!=', null)
            ->pluck('class_students.student_id')
            ->unique();

        $unassignedStudents = Student::query()
            ->whereNotIn('id', $enrolledStudentIds)
            ->orderBy('name')
            ->get()
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'studentNumber' => $student->student_number,
                'name' => $student->name,
                'email' => $student->email,
            ]);

        return Inertia::render('Academic/Classrooms/Enrollment', [
            'classroom' => ClassroomResource::make($classroom)->resolve(),
            'unassignedStudents' => $unassignedStudents,
        ]);
    }

    /**
     * Enroll students in a classroom.
     */
    public function enrollStudents(ClassroomEnrollStudentsRequest $request, Classroom $classroom): RedirectResponse
    {
        $validated = $request->validated();
        $studentIds = $validated['student_ids'];

        // Validate that students are not already enrolled in another active classroom in the same academic year
        $existingEnrollments = DB::table('class_students')
            ->join('classrooms', 'class_students.classroom_id', '=', 'classrooms.id')
            ->where('classrooms.academic_year_id', $classroom->academic_year_id)
            ->whereIn('class_students.student_id', $studentIds)
            ->where('class_students.status', EnrollmentStatus::Active->value)
            ->where('class_students.classroom_id', '!=', $classroom->id)
            ->pluck('class_students.student_id')
            ->unique();

        if ($existingEnrollments->isNotEmpty()) {
            $studentNames = Student::whereIn('id', $existingEnrollments)->pluck('name')->join(', ');

            return back()->withErrors([
                'student_ids' => "The following students are already enrolled in another active classroom: {$studentNames}",
            ]);
        }

        // Attach students with ACTIVE status
        $classroom->students()->syncWithoutDetaching(
            collect($studentIds)->mapWithKeys(fn ($studentId) => [
                $studentId => ['status' => EnrollmentStatus::Active->value],
            ])->toArray()
        );

        return to_route('classrooms.enrollment', $classroom)->with('success', 'Students enrolled successfully.');
    }

    /**
     * Remove a student from a classroom (soft delete from pivot).
     */
    public function removeStudent(Classroom $classroom, Student $student): RedirectResponse
    {
        $classroom->students()->updateExistingPivot($student->id, [
            'status' => EnrollmentStatus::Dropped->value,
        ]);

        return to_route('classrooms.enrollment', $classroom)->with('success', 'Student removed from classroom.');
    }
}

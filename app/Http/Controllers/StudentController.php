<?php

namespace App\Http\Controllers;

use App\Http\Requests\Students\StudentGuardianAttachRequest;
use App\Http\Requests\Students\StudentGuardianDetachRequest;
use App\Http\Requests\Students\StudentGuardianStoreAndAttachRequest;
use App\Http\Requests\Students\StudentGuardianUpdateRequest;
use App\Http\Requests\Students\StudentIndexRequest;
use App\Http\Requests\Students\StudentStoreRequest;
use App\Http\Requests\Students\StudentUpdateRequest;
use App\Http\Resources\StudentResource;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     */
    public function index(StudentIndexRequest $request): Response
    {
        $validated = $request->validated();

        $students = Student::query()
            ->when($validated['with_trashed'] === 'only', fn ($query) => $query->onlyTrashed())
            ->when($validated['with_trashed'] === 'all', fn ($query) => $query->withTrashed())
            ->when($validated['search'], function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->cursorPaginate($validated['per_page'])
            ->withQueryString()
            ->through(fn (Student $student) => StudentResource::make($student)->resolve());

        return Inertia::render('Students/Index', [
            'students' => $students,
            'perPageOptions' => config('pagination.per_page_options'),
        ]);
    }

    /**
     * Show the form for creating a new student.
     */
    public function create(): Response
    {
        return Inertia::render('Students/Create');
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(StudentStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $guardians = $validated['guardians'] ?? [];
        unset($validated['guardians']);

        $student = Student::create($validated);

        // Create and attach guardians if provided
        foreach ($guardians as $guardianData) {
            $relationshipType = $guardianData['relationship_type'] ?? null;
            $isPrimary = $guardianData['is_primary'] ?? false;

            // Handle existing guardian (by ID) or create new guardian
            if (isset($guardianData['guardian_id'])) {
                // Attach existing guardian
                $guardianId = $guardianData['guardian_id'];
                $student->guardians()->attach($guardianId, [
                    'relationship_type' => $relationshipType,
                    'is_primary' => $isPrimary,
                ]);
            } else {
                // Create new guardian and attach
                unset($guardianData['relationship_type'], $guardianData['is_primary']);
                $guardian = Guardian::create($guardianData);

                $student->guardians()->attach($guardian->id, [
                    'relationship_type' => $relationshipType,
                    'is_primary' => $isPrimary,
                ]);
            }
        }

        return to_route('students.edit', $student)->with('success', 'Student created.');
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student): Response
    {
        $student->load('guardians');

        return Inertia::render('Students/Show', [
            'student' => StudentResource::make($student)->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student): Response
    {
        $student->load('guardians');

        return Inertia::render('Students/Edit', [
            'student' => StudentResource::make($student)->resolve(),
        ]);
    }

    /**
     * Update the specified student in storage.
     */
    public function update(StudentUpdateRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());

        return to_route('students.edit', $student)->with('success', 'Student updated.');
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return to_route('students.index')->with('success', 'Student deleted.');
    }

    /**
     * Restore the specified soft-deleted student.
     */
    public function restore(string $student): RedirectResponse
    {
        $student = Student::withTrashed()->findOrFail($student);
        $student->restore();

        return to_route('students.index')->with('success', 'Student restored.');
    }

    /**
     * Permanently delete the specified student.
     */
    public function forceDelete(string $student): RedirectResponse
    {
        $student = Student::withTrashed()->findOrFail($student);
        $student->forceDelete();

        return to_route('students.index')->with('success', 'Student permanently deleted.');
    }

    /**
     * List guardians for a student.
     */
    public function guardians(Student $student): Response
    {
        $student->load('guardians');

        return Inertia::render('Students/Show', [
            'student' => StudentResource::make($student)->resolve(),
        ]);
    }

    /**
     * Attach a guardian to a student.
     */
    public function attachGuardian(StudentGuardianAttachRequest $request, Student $student): RedirectResponse
    {
        $validated = $request->validated();
        $guardian = Guardian::findOrFail($validated['guardian_id']);

        $student->guardians()->attach($guardian->id, [
            'relationship_type' => $validated['relationship_type'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        return to_route('students.show', $student)->with('success', 'Guardian attached successfully.');
    }

    /**
     * Detach a guardian from a student.
     */
    public function detachGuardian(StudentGuardianDetachRequest $request, Student $student, Guardian $guardian): RedirectResponse
    {
        $student->guardians()->detach($guardian->id);

        return to_route('students.show', $student)->with('success', 'Guardian detached successfully.');
    }

    /**
     * Update the relationship between a student and guardian.
     */
    public function updateGuardianRelationship(StudentGuardianUpdateRequest $request, Student $student, Guardian $guardian): RedirectResponse
    {
        $validated = $request->validated();

        $student->guardians()->updateExistingPivot($guardian->id, [
            'relationship_type' => $validated['relationship_type'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        return to_route('students.show', $student)->with('success', 'Guardian relationship updated successfully.');
    }

    /**
     * Create a new guardian and attach it to a student.
     */
    public function storeAndAttachGuardian(StudentGuardianStoreAndAttachRequest $request, Student $student): RedirectResponse
    {
        $validated = $request->validated();

        // Extract guardian fields
        $guardianData = [
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'relationship' => $validated['relationship'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        // Create the guardian
        $guardian = Guardian::create($guardianData);

        // Attach to student with relationship data
        $student->guardians()->attach($guardian->id, [
            'relationship_type' => $validated['relationship_type'] ?? null,
            'is_primary' => $validated['is_primary'] ?? false,
        ]);

        return to_route('students.edit', $student)->with('success', 'Guardian created and attached successfully.');
    }
}

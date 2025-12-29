<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\AcademicYearIndexRequest;
use App\Http\Requests\Academic\AcademicYearStoreRequest;
use App\Http\Requests\Academic\AcademicYearUpdateRequest;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the academic years.
     */
    public function index(AcademicYearIndexRequest $request): Response
    {
        $validated = $request->validated();

        $academicYears = AcademicYear::query()
            ->when($validated['with_trashed'] === 'only', fn ($query) => $query->onlyTrashed())
            ->when($validated['with_trashed'] === 'all', fn ($query) => $query->withTrashed())
            ->when($validated['search'], function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('start_date', 'desc')
            ->cursorPaginate($validated['per_page'])
            ->withQueryString()
            ->through(fn (AcademicYear $academicYear) => AcademicYearResource::make($academicYear)->resolve());

        return Inertia::render('Academic/AcademicYears/Index', [
            'academicYears' => $academicYears,
            'perPageOptions' => config('pagination.per_page_options'),
        ]);
    }

    /**
     * Show the form for creating a new academic year.
     */
    public function create(): Response
    {
        return Inertia::render('Academic/AcademicYears/Create');
    }

    /**
     * Store a newly created academic year in storage.
     */
    public function store(AcademicYearStoreRequest $request): RedirectResponse
    {
        $academicYear = AcademicYear::create($request->validated());

        return to_route('academic-years.edit', $academicYear)->with('success', 'Academic year created.');
    }

    /**
     * Display the specified academic year.
     */
    public function show(AcademicYear $academicYear): Response
    {
        $academicYear->load('classrooms.homeroomTeacher');

        return Inertia::render('Academic/AcademicYears/Show', [
            'academicYear' => AcademicYearResource::make($academicYear)->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified academic year.
     */
    public function edit(AcademicYear $academicYear): Response
    {
        return Inertia::render('Academic/AcademicYears/Edit', [
            'academicYear' => AcademicYearResource::make($academicYear)->resolve(),
        ]);
    }

    /**
     * Update the specified academic year in storage.
     */
    public function update(AcademicYearUpdateRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->update($request->validated());

        return to_route('academic-years.edit', $academicYear)->with('success', 'Academic year updated.');
    }

    /**
     * Remove the specified academic year from storage.
     */
    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->delete();

        return to_route('academic-years.index')->with('success', 'Academic year deleted.');
    }

    /**
     * Restore the specified soft-deleted academic year.
     */
    public function restore(string $academicYear): RedirectResponse
    {
        $academicYear = AcademicYear::withTrashed()->findOrFail($academicYear);
        $academicYear->restore();

        return to_route('academic-years.index')->with('success', 'Academic year restored.');
    }

    /**
     * Permanently delete the specified academic year.
     */
    public function forceDelete(string $academicYear): RedirectResponse
    {
        $academicYear = AcademicYear::withTrashed()->findOrFail($academicYear);
        $academicYear->forceDelete();

        return to_route('academic-years.index')->with('success', 'Academic year permanently deleted.');
    }

    /**
     * Toggle the active status of an academic year.
     */
    public function toggleActive(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->update(['is_active' => ! $academicYear->is_active]);

        return to_route('academic-years.index')->with('success', 'Academic year active status updated.');
    }
}

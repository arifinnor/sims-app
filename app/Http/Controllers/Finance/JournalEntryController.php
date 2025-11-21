<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Finance\JournalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\JournalEntryIndexRequest;
use App\Http\Resources\JournalEntryResource;
use App\Models\Finance\JournalEntry;
use App\Services\Finance\JournalEntryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of the journal entries.
     */
    public function index(JournalEntryIndexRequest $request): Response
    {
        $validated = $request->validated();

        $journalEntries = JournalEntry::query()
            ->with(['type', 'student', 'createdBy'])
            ->when($validated['search'], function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($validated['status'], function ($query, $status) {
                $statusEnum = JournalStatus::from($status);
                match ($statusEnum) {
                    JournalStatus::Draft => $query->draft(),
                    JournalStatus::Posted => $query->posted(),
                    JournalStatus::Void => $query->void(),
                };
            })
            ->latest('transaction_date')
            ->latest('id')
            ->cursorPaginate($validated['per_page'])
            ->withQueryString()
            ->through(fn (JournalEntry $entry) => JournalEntryResource::make($entry)->resolve());

        return Inertia::render('Finance/JournalEntries/Index', [
            'journalEntries' => $journalEntries,
            'perPageOptions' => config('pagination.per_page_options'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        //
    }

    /**
     * Void a journal entry (Immutable Ledger Principle).
     */
    public function void(JournalEntry $journalEntry, JournalEntryService $service): RedirectResponse
    {
        try {
            $service->void($journalEntry);

            return to_route('finance.journal-entries.index')->with('success', 'Journal entry voided successfully.');
        } catch (\Exception $e) {
            return to_route('finance.journal-entries.index')->with('error', $e->getMessage());
        }
    }
}

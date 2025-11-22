<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\TransactionIndexRequest;
use App\Http\Requests\Finance\TransactionStoreRequest;
use App\Http\Resources\Finance\TransactionTypeResource;
use App\Http\Resources\JournalEntryResource;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\TransactionType;
use App\Models\Student;
use App\Services\Finance\JournalEntryService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    /**
     * Show the form for creating a new transaction.
     */
    public function create(): Response
    {
        $transactionTypes = TransactionType::query()
            ->where('is_active', true)
            ->with(['accounts.chartOfAccount'])
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $cashAccounts = ChartOfAccount::query()
            ->where('is_cash', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->map(fn (ChartOfAccount $account) => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'display' => "{$account->code} - {$account->name}",
            ]);

        $students = Student::query()
            ->orderBy('name')
            ->get(['id', 'name', 'student_number'])
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->name,
                'student_number' => $student->student_number,
            ]);

        return Inertia::render('Finance/Transactions/Create', [
            'transactionTypes' => TransactionTypeResource::collection($transactionTypes)->resolve(),
            'cashAccounts' => $cashAccounts,
            'students' => $students,
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(TransactionStoreRequest $request, JournalEntryService $service): RedirectResponse
    {
        $validated = $request->validated();

        $journal = $service->create($validated['type_code'], [
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'cash_account_id' => $validated['cash_account_id'] ?? null,
            'student_id' => $validated['student_id'] ?? null,
            'external_ref' => $validated['external_ref'] ?? null,
        ]);

        return to_route('finance.transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Display a listing of transactions.
     */
    public function index(TransactionIndexRequest $request): Response
    {
        $validated = $request->validated();

        $journalEntries = JournalEntry::query()
            ->with(['type', 'lines.account', 'student', 'createdBy'])
            ->when($validated['search'], function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('reference_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($validated['date_from'], fn ($query, $date) => $query->where('transaction_date', '>=', $date))
            ->when($validated['date_to'], fn ($query, $date) => $query->where('transaction_date', '<=', $date))
            ->when($validated['transaction_type_id'], fn ($query, $typeId) => $query->where('transaction_type_id', $typeId))
            ->latest('transaction_date')
            ->latest('id')
            ->cursorPaginate($validated['per_page'])
            ->withQueryString()
            ->through(fn (JournalEntry $entry) => JournalEntryResource::make($entry)->resolve());

        $transactionTypes = TransactionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(fn (TransactionType $type) => [
                'id' => $type->id,
                'code' => $type->code,
                'name' => $type->name,
            ]);

        return Inertia::render('Finance/Transactions/Index', [
            'journalEntries' => $journalEntries,
            'transactionTypes' => $transactionTypes,
            'filters' => $validated,
            'perPageOptions' => config('pagination.per_page_options'),
        ]);
    }

    /**
     * Void a transaction.
     */
    public function void(JournalEntry $journalEntry, JournalEntryService $service): RedirectResponse
    {
        try {
            $service->void($journalEntry->id);

            return to_route('finance.transactions.index')
                ->with('success', 'Transaction voided successfully.');
        } catch (\Exception $e) {
            return to_route('finance.transactions.index')
                ->with('error', $e->getMessage());
        }
    }
}


<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\AccountIndexRequest;
use App\Http\Requests\Finance\AccountStoreRequest;
use App\Http\Requests\Finance\AccountUpdateRequest;
use App\Http\Resources\AccountResource;
use App\Models\Finance\Account;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    /**
     * Display a listing of the accounts.
     */
    public function index(AccountIndexRequest $request): Response
    {
        $validated = $request->validated();

        $accounts = Account::query()
            ->withCount('children')
            ->with(['parent', 'children'])
            ->when($validated['with_trashed'] === 'only', fn ($query) => $query->onlyTrashed())
            ->when($validated['with_trashed'] === 'all', fn ($query) => $query->withTrashed())
            ->when($validated['search'], function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('account_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($validated['type'], fn ($query, $type) => $query->where('type', $type))
            ->when($validated['status'], fn ($query, $status) => $query->where('status', $status))
            ->orderBy('account_number')
            ->orderBy('id')
            ->get()
            ->map(fn (Account $account) => AccountResource::make($account)->resolve())
            ->values()
            ->all();

        return Inertia::render('Finance/ChartOfAccounts/Index', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * Show the form for creating a new account.
     */
    public function create(): Response
    {
        $parentAccounts = Account::query()
            ->whereNull('parent_account_id')
            ->orderBy('account_number')
            ->get()
            ->map(fn (Account $account) => [
                'id' => $account->id,
                'accountNumber' => $account->account_number,
                'fullAccountNumber' => $account->getFullAccountNumber(),
                'name' => $account->name,
                'type' => $account->type,
            ]);

        return Inertia::render('Finance/ChartOfAccounts/Create', [
            'parentAccounts' => $parentAccounts,
        ]);
    }

    /**
     * Store a newly created account in storage.
     */
    public function store(AccountStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['currency'] = $validated['currency'] ?? 'IDR';
        $validated['status'] = $validated['status'] ?? 'active';
        $validated['balance'] = $validated['balance'] ?? 0;

        $account = Account::create($validated);

        return to_route('finance.accounts.edit', $account)->with('success', 'Account created.');
    }

    /**
     * Display the specified account.
     */
    public function show(Account $account): Response
    {
        $account->load(['parent', 'children']);

        return Inertia::render('Finance/ChartOfAccounts/Show', [
            'account' => AccountResource::make($account)->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified account.
     */
    public function edit(Account $account): Response
    {
        $parentAccounts = Account::query()
            ->where('id', '!=', $account->id)
            ->whereNull('parent_account_id')
            ->orderBy('account_number')
            ->get()
            ->map(fn (Account $parentAccount) => [
                'id' => $parentAccount->id,
                'accountNumber' => $parentAccount->account_number,
                'fullAccountNumber' => $parentAccount->getFullAccountNumber(),
                'name' => $parentAccount->name,
                'type' => $parentAccount->type,
            ]);

        return Inertia::render('Finance/ChartOfAccounts/Edit', [
            'account' => AccountResource::make($account)->resolve(),
            'parentAccounts' => $parentAccounts,
            'hasChildren' => $account->children()->exists(),
        ]);
    }

    /**
     * Update the specified account in storage.
     */
    public function update(AccountUpdateRequest $request, Account $account): RedirectResponse
    {
        $validated = $request->validated();

        // Prevent changing parent if account has children
        if ($account->children()->exists() && isset($validated['parent_account_id']) && $validated['parent_account_id'] !== $account->parent_account_id) {
            return back()->withErrors(['parent_account_id' => 'Cannot change parent account when account has children.']);
        }

        $account->update($validated);

        return to_route('finance.accounts.edit', $account)->with('success', 'Account updated.');
    }

    /**
     * Remove the specified account from storage.
     */
    public function destroy(Account $account): RedirectResponse
    {
        $account->delete();

        return to_route('finance.accounts.index')->with('success', 'Account deleted.');
    }

    /**
     * Restore the specified soft-deleted account.
     */
    public function restore(string $account): RedirectResponse
    {
        $account = Account::withTrashed()->findOrFail($account);
        $account->restore();

        return to_route('finance.accounts.index')->with('success', 'Account restored.');
    }

    /**
     * Permanently delete the specified account.
     */
    public function forceDelete(string $account): RedirectResponse
    {
        $account = Account::withTrashed()->findOrFail($account);
        $account->forceDelete();

        return to_route('finance.accounts.index')->with('success', 'Account permanently deleted.');
    }
}

<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Finance\AccountType;
use App\Enums\Finance\NormalBalance;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\ChartOfAccountIndexRequest;
use App\Http\Requests\Finance\ChartOfAccountStoreRequest;
use App\Http\Requests\Finance\ChartOfAccountUpdateRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Models\Finance\AccountCategory;
use App\Models\Finance\ChartOfAccount;
use App\Services\Finance\ChartOfAccountService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(ChartOfAccountIndexRequest $request): Response
    {
        $filters = $request->validated();

        $query = ChartOfAccount::query()
            ->with([
                'category:id,name,sequence',
                'parent:id,code,name',
            ])
            ->when($filters['search'], function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['account_type'], fn ($query, $type) => $query->where('account_type', $type))
            ->when($filters['category_id'], fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when(! is_null($filters['is_posting']), fn ($query) => $query->where('is_posting', $filters['is_posting']))
            ->when(! is_null($filters['is_cash']), fn ($query) => $query->where('is_cash', $filters['is_cash']))
            ->when(! is_null($filters['is_active']), fn ($query) => $query->where('is_active', $filters['is_active']));

        $this->applyTrashedFilter($query, $filters['with_trashed']);

        $accounts = (clone $query)
            ->orderBy('code')
            ->cursorPaginate($filters['per_page'])
            ->withQueryString()
            ->through(fn (ChartOfAccount $account) => ChartOfAccountResource::make($account)->resolve());

        $treeCollection = (clone $query)->orderBy('code')->get();
        $tree = $this->buildTree($treeCollection);

        $categories = AccountCategory::query()
            ->orderBy('sequence')
            ->get(['id', 'name', 'sequence'])
            ->map(fn (AccountCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ]);

        return Inertia::render('Finance/ChartOfAccounts/Index', [
            'accounts' => $accounts,
            'accountsTree' => $tree,
            'filters' => $filters,
            'perPageOptions' => config('pagination.per_page_options'),
            'accountTypes' => AccountType::values(),
            'normalBalances' => NormalBalance::values(),
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Finance/ChartOfAccounts/Create', $this->formOptions());
    }

    /**
     * Get the next suggested code for a chart of account.
     */
    public function getNextCode(Request $request, ChartOfAccountService $service): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => ['sometimes', 'nullable', 'uuid', 'exists:chart_of_accounts,id'],
        ]);

        try {
            $code = $service->suggestNextCode($validated['parent_id'] ?? null);

            return response()->json(['code' => $code]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Parent account not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to suggest next code.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ChartOfAccountStoreRequest $request): RedirectResponse
    {
        $account = ChartOfAccount::create($request->validated());

        return to_route('finance.chart-of-accounts.show', $account)
            ->with('success', 'Account created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount): Response
    {
        $chartOfAccount->load([
            'category:id,name,sequence',
            'parent:id,code,name',
            'children' => fn ($query) => $query->with('category:id,name')->orderBy('code'),
        ]);

        return Inertia::render('Finance/ChartOfAccounts/Show', [
            'account' => ChartOfAccountResource::make($chartOfAccount)->resolve(),
            'children' => ChartOfAccountResource::collection($chartOfAccount->children)->resolve(),
            'breadcrumbs' => $this->buildBreadcrumbs($chartOfAccount),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartOfAccount $chartOfAccount): Response
    {
        $chartOfAccount->load(['category:id,name,sequence', 'parent:id,code,name']);

        return Inertia::render('Finance/ChartOfAccounts/Edit', array_merge(
            [
                'account' => ChartOfAccountResource::make($chartOfAccount)->resolve(),
            ],
            $this->formOptions($chartOfAccount)
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ChartOfAccountUpdateRequest $request, ChartOfAccount $chartOfAccount): RedirectResponse
    {
        $chartOfAccount->update($request->validated());

        return to_route('finance.chart-of-accounts.show', $chartOfAccount)
            ->with('success', 'Account updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chartOfAccount): RedirectResponse
    {
        $chartOfAccount->delete();

        return to_route('finance.chart-of-accounts.index')
            ->with('success', 'Account archived.');
    }

    /**
     * Restore the specified soft-deleted account.
     */
    public function restore(string $chartOfAccount): RedirectResponse
    {
        $account = ChartOfAccount::withTrashed()->findOrFail($chartOfAccount);
        $account->restore();

        return to_route('finance.chart-of-accounts.show', $account)
            ->with('success', 'Account restored.');
    }

    /**
     * Permanently delete the specified resource.
     */
    public function forceDelete(string $chartOfAccount): RedirectResponse
    {
        $account = ChartOfAccount::withTrashed()->findOrFail($chartOfAccount);
        $account->forceDelete();

        return to_route('finance.chart-of-accounts.index')
            ->with('success', 'Account permanently deleted.');
    }

    /**
     * Build a hierarchical tree from the given accounts.
     *
     * @param  Collection<int, ChartOfAccount>  $accounts
     * @return array<int, mixed>
     */
    private function buildTree(Collection $accounts): array
    {
        $grouped = $accounts->groupBy('parent_id');

        $buildBranch = function ($parentId) use (&$buildBranch, $grouped) {
            return $grouped->get($parentId, collect())
                ->sortBy('code')
                ->map(function (ChartOfAccount $account) use (&$buildBranch) {
                    $node = ChartOfAccountResource::make($account)->resolve();
                    $node['children'] = $buildBranch($account->getKey());
                    $node['has_children'] = ! empty($node['children']);

                    return $node;
                })
                ->values()
                ->all();
        };

        return $buildBranch(null);
    }

    /**
     * Build breadcrumb data for the specified account.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildBreadcrumbs(ChartOfAccount $account): array
    {
        $breadcrumbs = [];
        $current = $account->parent;

        while ($current) {
            $breadcrumbs[] = [
                'id' => $current->id,
                'code' => $current->code,
                'name' => $current->name,
            ];
            $current = $current->parent()->with('parent')->first();
        }

        return array_reverse($breadcrumbs);
    }

    /**
     * Retrieve reusable form options.
     *
     * @return array<string, mixed>
     */
    private function formOptions(?ChartOfAccount $chartOfAccount = null): array
    {
        $parentOptions = ChartOfAccount::query()
            ->when($chartOfAccount, fn ($query) => $query->whereKeyNot($chartOfAccount->getKey()))
            ->where('is_posting', false)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'parent_id'])
            ->map(fn (ChartOfAccount $account) => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
            ])
            ->values();

        $categories = AccountCategory::query()
            ->orderBy('sequence')
            ->get(['id', 'name'])
            ->map(fn (AccountCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
            ]);

        return [
            'parentOptions' => $parentOptions,
            'categories' => $categories,
            'accountTypes' => AccountType::values(),
            'normalBalances' => NormalBalance::values(),
        ];
    }

    private function applyTrashedFilter($query, string $mode): void
    {
        if ($mode === 'only') {
            $query->onlyTrashed();
        } elseif ($mode === 'all') {
            $query->withTrashed();
        }
    }
}

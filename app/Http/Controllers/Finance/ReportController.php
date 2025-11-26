<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\GeneralLedgerRequest;
use App\Http\Requests\Finance\TrialBalanceRequest;
use App\Models\Finance\ChartOfAccount;
use App\Services\Reporting\GeneralLedgerService;
use App\Services\Reporting\TrialBalanceService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Display the reports index page.
     */
    public function index(): Response
    {
        return Inertia::render('Finance/Reports/Index');
    }

    /**
     * Display the General Ledger report.
     */
    public function generalLedger(GeneralLedgerRequest $request, GeneralLedgerService $service): Response
    {
        $validated = $request->validated();

        $ledgerData = $service->getLedger(
            $validated['account_id'],
            $validated['start_date'],
            $validated['end_date']
        );

        $accounts = ChartOfAccount::query()
            ->posting()
            ->active()
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return Inertia::render('Finance/Reports/GeneralLedger', [
            'account' => [
                'id' => $ledgerData['account']->id,
                'code' => $ledgerData['account']->code,
                'name' => $ledgerData['account']->name,
                'normal_balance' => $ledgerData['account']->normal_balance->value,
            ],
            'opening_balance' => $ledgerData['opening_balance'],
            'transactions' => $ledgerData['transactions'],
            'closing_balance' => $ledgerData['closing_balance'],
            'filters' => [
                'account_id' => $validated['account_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ],
            'accounts' => $accounts,
        ]);
    }

    /**
     * Display the General Ledger report index (filter form).
     */
    public function generalLedgerIndex(): Response
    {
        $accounts = ChartOfAccount::query()
            ->posting()
            ->active()
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return Inertia::render('Finance/Reports/GeneralLedger', [
            'accounts' => $accounts,
            'account' => null,
            'opening_balance' => null,
            'transactions' => [],
            'closing_balance' => null,
            'filters' => [
                'account_id' => null,
                'start_date' => null,
                'end_date' => null,
            ],
        ]);
    }

    /**
     * Display the Trial Balance report.
     */
    public function trialBalance(TrialBalanceRequest $request, TrialBalanceService $service): Response
    {
        $validated = $request->validated();

        $accounts = $service->getReport(
            $validated['start_date'],
            $validated['end_date']
        );

        // Calculate grand totals
        $totalDebitMutation = $accounts->sum(fn ($account) => (float) $account->debit_mutation);
        $totalCreditMutation = $accounts->sum(fn ($account) => (float) $account->credit_mutation);

        // Verify totals match (double-entry bookkeeping principle)
        $difference = abs($totalDebitMutation - $totalCreditMutation);
        if ($difference > 0.01) {
            Log::warning('Trial Balance totals mismatch', [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'total_debit' => $totalDebitMutation,
                'total_credit' => $totalCreditMutation,
                'difference' => $difference,
            ]);
        }

        return Inertia::render('Finance/Reports/TrialBalance', [
            'accounts' => $accounts->map(function ($account) {
                return [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'normal_balance' => $account->normal_balance,
                    'category_id' => $account->category_id,
                    'category_name' => $account->category_name,
                    'category_sequence' => $account->category_sequence,
                    'opening_balance' => $account->opening_balance,
                    'debit_mutation' => $account->debit_mutation,
                    'credit_mutation' => $account->credit_mutation,
                    'closing_balance' => $account->closing_balance,
                ];
            })->values(),
            'total_debit_mutation' => number_format($totalDebitMutation, 2, '.', ''),
            'total_credit_mutation' => number_format($totalCreditMutation, 2, '.', ''),
            'filters' => [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ],
        ]);
    }

    /**
     * Display the Trial Balance report index (filter form).
     */
    public function trialBalanceIndex(): Response
    {
        $getCurrentMonthRange = function () {
            $now = new \DateTime;
            $start = new \DateTime($now->format('Y-m-01'));
            $end = new \DateTime($now->format('Y-m-t'));

            return [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ];
        };

        $dateRange = $getCurrentMonthRange();

        return Inertia::render('Finance/Reports/TrialBalance', [
            'accounts' => [],
            'total_debit_mutation' => '0.00',
            'total_credit_mutation' => '0.00',
            'filters' => [
                'start_date' => $dateRange['start'],
                'end_date' => $dateRange['end'],
            ],
        ]);
    }
}

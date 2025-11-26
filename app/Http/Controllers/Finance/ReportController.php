<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\GeneralLedgerRequest;
use App\Models\Finance\ChartOfAccount;
use App\Services\Reporting\GeneralLedgerService;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
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
}

<?php

namespace App\Services\Reporting;

use App\Enums\Finance\EntryDirection;
use App\Enums\Finance\JournalStatus;
use App\Enums\Finance\TransactionCategory;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntryLine;
use Illuminate\Support\Collection;

class CashFlowService
{
    /**
     * Activity types for cash flow categorization.
     */
    private const OPERATING_CATEGORIES = [
        TransactionCategory::Income,
        TransactionCategory::Expense,
        TransactionCategory::Billing,
        TransactionCategory::Payroll,
    ];

    private const INVESTING_CATEGORIES = [
        TransactionCategory::Asset,
    ];

    private const FINANCING_CATEGORIES = [
        TransactionCategory::Liability,
    ];

    /**
     * Get cash flow report for the specified date range.
     *
     * @return array{
     *     operating_activities: Collection<int, object>,
     *     operating_total: string,
     *     investing_activities: Collection<int, object>,
     *     investing_total: string,
     *     financing_activities: Collection<int, object>,
     *     financing_total: string,
     *     net_change_in_cash: string,
     *     beginning_cash_balance: string,
     *     ending_cash_balance: string
     * }
     */
    public function getReport(string $startDate, string $endDate): array
    {
        // Get all cash account IDs
        $cashAccountIds = ChartOfAccount::query()
            ->cash()
            ->posting()
            ->active()
            ->pluck('id')
            ->toArray();

        if (empty($cashAccountIds)) {
            return $this->emptyResult();
        }

        // Calculate beginning cash balance (all transactions before start date)
        $beginningCashBalance = $this->calculateCashBalanceAsOf($cashAccountIds, $startDate, false);

        // Fetch all cash transactions within the date range
        $cashLines = $this->fetchCashTransactions($cashAccountIds, $startDate, $endDate);

        // Categorize transactions by activity type
        $categorizedActivities = $this->categorizeTransactions($cashLines);

        // Calculate totals
        $operatingTotal = $categorizedActivities['operating']->sum('amount');
        $investingTotal = $categorizedActivities['investing']->sum('amount');
        $financingTotal = $categorizedActivities['financing']->sum('amount');

        $netChangeInCash = $operatingTotal + $investingTotal + $financingTotal;
        $endingCashBalance = $beginningCashBalance + $netChangeInCash;

        return [
            'operating_activities' => $categorizedActivities['operating'],
            'operating_total' => $this->formatAmount($operatingTotal),
            'investing_activities' => $categorizedActivities['investing'],
            'investing_total' => $this->formatAmount($investingTotal),
            'financing_activities' => $categorizedActivities['financing'],
            'financing_total' => $this->formatAmount($financingTotal),
            'net_change_in_cash' => $this->formatAmount($netChangeInCash),
            'beginning_cash_balance' => $this->formatAmount($beginningCashBalance),
            'ending_cash_balance' => $this->formatAmount($endingCashBalance),
        ];
    }

    /**
     * Fetch all cash transactions within the date range.
     *
     * @param  array<string>  $cashAccountIds
     * @return Collection<int, JournalEntryLine>
     */
    private function fetchCashTransactions(array $cashAccountIds, string $startDate, string $endDate): Collection
    {
        return JournalEntryLine::query()
            ->with(['journal.type', 'account'])
            ->whereIn('chart_of_account_id', $cashAccountIds)
            ->whereHas('journal', function ($query) use ($startDate, $endDate) {
                $query->where('status', JournalStatus::Posted)
                    ->whereBetween('transaction_date', [$startDate, $endDate]);
            })
            ->get();
    }

    /**
     * Categorize transactions by activity type based on transaction category.
     *
     * @param  Collection<int, JournalEntryLine>  $cashLines
     * @return array{
     *     operating: Collection<int, object>,
     *     investing: Collection<int, object>,
     *     financing: Collection<int, object>
     * }
     */
    private function categorizeTransactions(Collection $cashLines): array
    {
        $operating = collect();
        $investing = collect();
        $financing = collect();

        // Group by transaction type
        $groupedByType = $cashLines->groupBy(function ($line) {
            return $line->journal->type?->id ?? 'unknown';
        });

        foreach ($groupedByType as $typeId => $lines) {
            $firstLine = $lines->first();
            $transactionType = $firstLine->journal->type;

            if (! $transactionType) {
                continue;
            }

            // Calculate net cash flow for this transaction type
            $netAmount = $lines->sum(function ($line) {
                return $this->calculateCashFlow($line);
            });

            if (abs($netAmount) < 0.01) {
                continue;
            }

            $activityItem = (object) [
                'transaction_type_id' => $transactionType->id,
                'transaction_type_name' => $transactionType->name,
                'transaction_type_code' => $transactionType->code,
                'category' => $transactionType->category->value,
                'amount' => $netAmount,
                'formatted_amount' => $this->formatAmount($netAmount),
                'transaction_count' => $lines->count(),
            ];

            // Categorize based on transaction category
            $category = $transactionType->category;

            if (in_array($category, self::OPERATING_CATEGORIES, true)) {
                $operating->push($activityItem);
            } elseif (in_array($category, self::INVESTING_CATEGORIES, true)) {
                $investing->push($activityItem);
            } elseif (in_array($category, self::FINANCING_CATEGORIES, true)) {
                $financing->push($activityItem);
            } elseif ($category === TransactionCategory::Transfer) {
                // Transfer transactions are categorized as operating
                $operating->push($activityItem);
            } else {
                // Default to operating for any uncategorized
                $operating->push($activityItem);
            }
        }

        return [
            'operating' => $operating->sortBy('transaction_type_name')->values(),
            'investing' => $investing->sortBy('transaction_type_name')->values(),
            'financing' => $financing->sortBy('transaction_type_name')->values(),
        ];
    }

    /**
     * Calculate cash flow from a journal entry line.
     * DEBIT on cash account = Cash In (positive)
     * CREDIT on cash account = Cash Out (negative)
     */
    private function calculateCashFlow(JournalEntryLine $line): float
    {
        $amount = (float) $line->amount;

        return $line->direction === EntryDirection::Debit
            ? $amount
            : -$amount;
    }

    /**
     * Calculate cumulative cash balance as of a specific date.
     *
     * @param  array<string>  $cashAccountIds
     * @param  bool  $inclusive  Whether to include the date itself
     */
    private function calculateCashBalanceAsOf(array $cashAccountIds, string $date, bool $inclusive = true): float
    {
        if (empty($cashAccountIds)) {
            return 0.0;
        }

        $query = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->whereIn('journal_entry_lines.chart_of_account_id', $cashAccountIds)
            ->where('journal_entries.status', JournalStatus::Posted);

        if ($inclusive) {
            $query->where('journal_entries.transaction_date', '<=', $date);
        } else {
            $query->where('journal_entries.transaction_date', '<', $date);
        }

        $result = $query->selectRaw('
            COALESCE(SUM(CASE WHEN journal_entry_lines.direction = ? THEN journal_entry_lines.amount ELSE 0 END), 0) as total_debit,
            COALESCE(SUM(CASE WHEN journal_entry_lines.direction = ? THEN journal_entry_lines.amount ELSE 0 END), 0) as total_credit
        ', [EntryDirection::Debit->value, EntryDirection::Credit->value])
            ->first();

        // Cash balance = Total Debits - Total Credits (since cash is an asset account)
        return (float) $result->total_debit - (float) $result->total_credit;
    }

    /**
     * Format amount as a decimal string with 2 decimal places.
     */
    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }

    /**
     * Return empty result structure.
     *
     * @return array<string, mixed>
     */
    private function emptyResult(): array
    {
        return [
            'operating_activities' => collect(),
            'operating_total' => '0.00',
            'investing_activities' => collect(),
            'investing_total' => '0.00',
            'financing_activities' => collect(),
            'financing_total' => '0.00',
            'net_change_in_cash' => '0.00',
            'beginning_cash_balance' => '0.00',
            'ending_cash_balance' => '0.00',
        ];
    }
}

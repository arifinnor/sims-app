<?php

use App\Enums\Finance\EntryDirection;
use App\Enums\Finance\JournalStatus;
use App\Enums\Finance\ReportType;
use App\Enums\Finance\TransactionCategory;
use App\Models\Finance\AccountCategory;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\JournalEntry;
use App\Models\Finance\JournalEntryLine;
use App\Models\Finance\TransactionType;
use App\Models\User;
use App\Services\Reporting\CashFlowService;

beforeEach(function () {
    $this->service = app(CashFlowService::class);
});

describe('CashFlowService', function () {
    it('returns empty report when no cash accounts exist', function () {
        $result = $this->service->getReport('2025-01-01', '2025-12-31');

        expect($result['operating_activities'])->toBeEmpty()
            ->and($result['investing_activities'])->toBeEmpty()
            ->and($result['financing_activities'])->toBeEmpty()
            ->and($result['operating_total'])->toBe('0.00')
            ->and($result['investing_total'])->toBe('0.00')
            ->and($result['financing_total'])->toBe('0.00')
            ->and($result['net_change_in_cash'])->toBe('0.00')
            ->and($result['beginning_cash_balance'])->toBe('0.00')
            ->and($result['ending_cash_balance'])->toBe('0.00');
    });

    it('calculates cash inflow from debit entries on cash accounts', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
            'name' => 'Cash',
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Income,
            'code' => 'INC-001',
            'name' => 'Cash Revenue',
        ]);

        $journal = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);

        // Cash Debit = Cash In (positive)
        JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 5000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['operating_activities'])->toHaveCount(1)
            ->and($result['operating_total'])->toBe('5000.00')
            ->and($result['net_change_in_cash'])->toBe('5000.00')
            ->and($result['ending_cash_balance'])->toBe('5000.00');
    });

    it('calculates cash outflow from credit entries on cash accounts', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
            'name' => 'Cash',
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Expense,
            'code' => 'EXP-001',
            'name' => 'Office Expense',
        ]);

        $journal = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);

        // Cash Credit = Cash Out (negative)
        JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Credit,
            'amount' => 3000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['operating_activities'])->toHaveCount(1)
            ->and($result['operating_total'])->toBe('-3000.00')
            ->and($result['net_change_in_cash'])->toBe('-3000.00')
            ->and($result['ending_cash_balance'])->toBe('-3000.00');
    });

    it('categorizes income transactions as operating activities', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Income,
            'name' => 'Revenue Receipt',
        ]);

        $journal = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);

        JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 1000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['operating_activities'])->toHaveCount(1)
            ->and($result['operating_activities']->first()->category)->toBe('INCOME')
            ->and($result['operating_total'])->toBe('1000.00');
    });

    it('categorizes expense transactions as operating activities', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Expense,
            'name' => 'Utility Payment',
        ]);

        $journal = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);

        JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Credit,
            'amount' => 500.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['operating_activities'])->toHaveCount(1)
            ->and($result['operating_activities']->first()->category)->toBe('EXPENSE')
            ->and($result['operating_total'])->toBe('-500.00');
    });

    it('categorizes billing transactions as operating activities', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Billing,
            'name' => 'Tuition Receipt',
        ]);

        $journal = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);

        JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 2000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['operating_activities'])->toHaveCount(1)
            ->and($result['operating_activities']->first()->category)->toBe('BILLING');
    });

    it('categorizes payroll transactions as operating activities', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Payroll,
            'name' => 'Salary Payment',
        ]);

        $journal = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);

        JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Credit,
            'amount' => 3500.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['operating_activities'])->toHaveCount(1)
            ->and($result['operating_activities']->first()->category)->toBe('PAYROLL');
    });

    it('categorizes asset transactions as investing activities', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Asset,
            'name' => 'Equipment Purchase',
        ]);

        $journal = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);

        JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Credit,
            'amount' => 10000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['investing_activities'])->toHaveCount(1)
            ->and($result['investing_activities']->first()->category)->toBe('ASSET')
            ->and($result['investing_total'])->toBe('-10000.00');
    });

    it('categorizes liability transactions as financing activities', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Liability,
            'name' => 'Bank Loan Receipt',
        ]);

        $journal = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);

        JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 50000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['financing_activities'])->toHaveCount(1)
            ->and($result['financing_activities']->first()->category)->toBe('LIABILITY')
            ->and($result['financing_total'])->toBe('50000.00');
    });

    it('calculates beginning cash balance from transactions before date range', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Income,
        ]);

        // Transaction BEFORE the date range
        $journal1 = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2024-12-15',
            'status' => JournalStatus::Posted,
        ]);
        JournalEntryLine::factory()->forJournal($journal1)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 10000.00,
        ]);

        // Transaction WITHIN the date range
        $journal2 = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);
        JournalEntryLine::factory()->forJournal($journal2)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 5000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['beginning_cash_balance'])->toBe('10000.00')
            ->and($result['net_change_in_cash'])->toBe('5000.00')
            ->and($result['ending_cash_balance'])->toBe('15000.00');
    });

    it('excludes voided transactions', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Income,
        ]);

        // Posted transaction
        $journal1 = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);
        JournalEntryLine::factory()->forJournal($journal1)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 5000.00,
        ]);

        // Voided transaction - should be excluded
        $journal2 = JournalEntry::factory()->void()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-20',
            'status' => JournalStatus::Void,
        ]);
        JournalEntryLine::factory()->forJournal($journal2)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 10000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['net_change_in_cash'])->toBe('5000.00');
    });

    it('excludes draft transactions', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Income,
        ]);

        // Posted transaction
        $journal1 = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);
        JournalEntryLine::factory()->forJournal($journal1)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 3000.00,
        ]);

        // Draft transaction - should be excluded
        $journal2 = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-20',
            'status' => JournalStatus::Draft,
        ]);
        JournalEntryLine::factory()->forJournal($journal2)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 7000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['net_change_in_cash'])->toBe('3000.00');
    });

    it('excludes transactions outside date range', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Income,
        ]);

        // Transaction in range
        $journal1 = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);
        JournalEntryLine::factory()->forJournal($journal1)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 5000.00,
        ]);

        // Transaction AFTER range (should be excluded from net change, but not from beginning)
        $journal2 = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-02-15',
            'status' => JournalStatus::Posted,
        ]);
        JournalEntryLine::factory()->forJournal($journal2)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 8000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        expect($result['net_change_in_cash'])->toBe('5000.00')
            ->and($result['ending_cash_balance'])->toBe('5000.00');
    });

    it('ignores non-cash accounts', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
            'name' => 'Cash',
        ]);
        $nonCashAccount = ChartOfAccount::factory()->asset()->create([
            'category_id' => $category->id,
            'name' => 'Accounts Receivable',
            'is_cash' => false,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Income,
        ]);

        $journal = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);

        // Cash account entry
        JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 5000.00,
        ]);

        // Non-cash account entry (should be ignored)
        JournalEntryLine::factory()->forJournal($journal)->forAccount($nonCashAccount)->create([
            'direction' => EntryDirection::Credit,
            'amount' => 5000.00,
        ]);

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        // Only the cash account entry should be counted
        expect($result['net_change_in_cash'])->toBe('5000.00');
    });

    it('aggregates multiple transactions of same type', function () {
        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Income,
            'name' => 'Tuition Payment',
        ]);

        // Multiple transactions of same type
        for ($i = 1; $i <= 3; $i++) {
            $journal = JournalEntry::factory()->create([
                'transaction_type_id' => $transactionType->id,
                'transaction_date' => "2025-01-{$i}0",
                'status' => JournalStatus::Posted,
            ]);
            JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
                'direction' => EntryDirection::Debit,
                'amount' => 1000.00,
            ]);
        }

        $result = $this->service->getReport('2025-01-01', '2025-01-31');

        // Should be aggregated into one activity item
        expect($result['operating_activities'])->toHaveCount(1)
            ->and($result['operating_activities']->first()->transaction_count)->toBe(3)
            ->and($result['operating_total'])->toBe('3000.00');
    });
});

describe('Cash Flow Controller', function () {
    it('shows cash flow index page', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('finance.reports.cash-flow.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Finance/Reports/CashFlow')
                ->has('filters.start_date')
                ->has('filters.end_date')
                ->where('operating_total', '0.00')
                ->where('investing_total', '0.00')
                ->where('financing_total', '0.00')
                ->where('net_change_in_cash', '0.00')
                ->where('beginning_cash_balance', '0.00')
                ->where('ending_cash_balance', '0.00')
            );
    });

    it('shows cash flow report with valid filters', function () {
        $user = User::factory()->create();

        $category = AccountCategory::factory()->create([
            'report_type' => ReportType::BalanceSheet,
        ]);
        $cashAccount = ChartOfAccount::factory()->cash()->create([
            'category_id' => $category->id,
        ]);

        $transactionType = TransactionType::factory()->create([
            'category' => TransactionCategory::Income,
        ]);

        $journal = JournalEntry::factory()->create([
            'transaction_type_id' => $transactionType->id,
            'transaction_date' => '2025-01-15',
            'status' => JournalStatus::Posted,
        ]);
        JournalEntryLine::factory()->forJournal($journal)->forAccount($cashAccount)->create([
            'direction' => EntryDirection::Debit,
            'amount' => 5000.00,
        ]);

        $this->actingAs($user)
            ->get(route('finance.reports.cash-flow.show', [
                'start_date' => '2025-01-01',
                'end_date' => '2025-01-31',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Finance/Reports/CashFlow')
                ->has('operating_activities', 1)
                ->where('operating_total', '5000.00')
                ->where('net_change_in_cash', '5000.00')
                ->where('ending_cash_balance', '5000.00')
            );
    });

    it('validates required date fields', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('finance.reports.cash-flow.show'))
            ->assertRedirect()
            ->assertSessionHasErrors(['start_date', 'end_date']);
    });

    it('validates start date is before end date', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('finance.reports.cash-flow.show', [
                'start_date' => '2025-01-31',
                'end_date' => '2025-01-01',
            ]))
            ->assertRedirect()
            ->assertSessionHasErrors(['start_date', 'end_date']);
    });

    it('requires authentication', function () {
        $this->get(route('finance.reports.cash-flow.index'))
            ->assertRedirect(route('login'));
    });
});

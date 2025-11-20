<?php

use App\Enums\Finance\AccountType;
use App\Enums\Finance\NormalBalance;
use App\Enums\Finance\TransactionCategory;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\TransactionType;
use Database\Seeders\Finance\ChartOfAccountSeeder;
use Database\Seeders\Finance\TransactionTypeSeeder;

beforeEach(function () {
    $this->seed(ChartOfAccountSeeder::class);
    $this->seed(TransactionTypeSeeder::class);
});

it('creates all required liability accounts', function () {
    $accounts = [
        ['code' => '2-1103', 'name' => 'Tabungan Siswa', 'type' => AccountType::Liability],
        ['code' => '2-1104', 'name' => 'Pendapatan Diterima Dimuka', 'type' => AccountType::Liability],
        ['code' => '2-1105', 'name' => 'Utang PPh 21', 'type' => AccountType::Liability],
    ];

    foreach ($accounts as $accountData) {
        $account = ChartOfAccount::where('code', $accountData['code'])->first();

        expect($account)->not->toBeNull()
            ->and($account->name)->toBe($accountData['name'])
            ->and($account->account_type)->toBe($accountData['type'])
            ->and($account->normal_balance)->toBe(NormalBalance::Credit)
            ->and($account->is_posting)->toBeTrue();
    }
});

it('creates all required revenue accounts', function () {
    $accounts = [
        ['code' => '4-1104', 'name' => 'Penjualan Seragam & Buku'],
        ['code' => '4-1105', 'name' => 'Pendapatan Ekskul/Kegiatan'],
        ['code' => '4-2101', 'name' => 'Dana BOS (Bantuan Operasional Sekolah)'],
        ['code' => '4-2102', 'name' => 'Sumbangan/Donasi'],
    ];

    foreach ($accounts as $accountData) {
        $account = ChartOfAccount::where('code', $accountData['code'])->first();

        expect($account)->not->toBeNull()
            ->and($account->name)->toBe($accountData['name'])
            ->and($account->account_type)->toBe(AccountType::Revenue)
            ->and($account->normal_balance)->toBe(NormalBalance::Credit)
            ->and($account->is_posting)->toBeTrue();
    }
});

it('creates other revenue header account', function () {
    $account = ChartOfAccount::where('code', '4-2000')->first();

    expect($account)->not->toBeNull()
        ->and($account->name)->toBe('Other Revenue')
        ->and($account->account_type)->toBe(AccountType::Revenue)
        ->and($account->is_posting)->toBeFalse();
});

it('creates all required expense accounts', function () {
    $accounts = [
        ['code' => '6-1103', 'name' => 'Beban Listrik, Air & Internet'],
        ['code' => '6-1104', 'name' => 'Beban Pemeliharaan Gedung'],
        ['code' => '6-1105', 'name' => 'Beban Keamanan & Kebersihan'],
        ['code' => '6-1106', 'name' => 'Beban Pemasaran & Promosi'],
        ['code' => '6-1107', 'name' => 'Beban Penyusutan Aset'],
        ['code' => '6-1108', 'name' => 'Beban Perlengkapan/ATK'],
    ];

    foreach ($accounts as $accountData) {
        $account = ChartOfAccount::where('code', $accountData['code'])->first();

        expect($account)->not->toBeNull()
            ->and($account->name)->toBe($accountData['name'])
            ->and($account->account_type)->toBe(AccountType::Expense)
            ->and($account->normal_balance)->toBe(NormalBalance::Debit)
            ->and($account->is_posting)->toBeTrue();
    }
});

it('creates tuition billing transaction type with mapped accounts', function () {
    $transactionType = TransactionType::where('code', 'TUITION_BILLING')->first();

    expect($transactionType)->not->toBeNull()
        ->and($transactionType->name)->toBe('Tagihan SPP Bulanan')
        ->and($transactionType->category)->toBe(TransactionCategory::Billing)
        ->and($transactionType->is_system)->toBeTrue();

    $receivableConfig = $transactionType->configs()->where('config_key', 'receivable_debit')->first();
    $revenueConfig = $transactionType->configs()->where('config_key', 'revenue_credit')->first();

    expect($receivableConfig)->not->toBeNull()
        ->and($receivableConfig->account->code)->toBe('1-1103')
        ->and($revenueConfig)->not->toBeNull()
        ->and($revenueConfig->account->code)->toBe('4-1101');
});

it('creates admission registration transaction type with mapped accounts', function () {
    $transactionType = TransactionType::where('code', 'ADMISSION_REGISTRATION')->first();

    expect($transactionType)->not->toBeNull()
        ->and($transactionType->name)->toBe('Pendaftaran PPDB (Uang Pangkal)')
        ->and($transactionType->category)->toBe(TransactionCategory::Billing);

    $receivableConfig = $transactionType->configs()->where('config_key', 'receivable_debit')->first();
    $revenueConfig = $transactionType->configs()->where('config_key', 'revenue_credit')->first();

    expect($receivableConfig->account->code)->toBe('1-1103')
        ->and($revenueConfig->account->code)->toBe('4-1102');
});

it('creates uniform sales transaction type with mapped revenue account', function () {
    $transactionType = TransactionType::where('code', 'UNIFORM_SALES')->first();

    expect($transactionType)->not->toBeNull()
        ->and($transactionType->name)->toBe('Penjualan Seragam & Buku')
        ->and($transactionType->category)->toBe(TransactionCategory::Income);

    $revenueConfig = $transactionType->configs()->where('config_key', 'revenue_credit')->first();
    $cashConfig = $transactionType->configs()->where('config_key', 'cash_debit')->first();

    expect($revenueConfig->account->code)->toBe('4-1104')
        ->and($cashConfig->account_id)->toBeNull(); // Dynamic user selection
});

it('creates student saving deposit transaction type with mapped liability account', function () {
    $transactionType = TransactionType::where('code', 'STUDENT_SAVING_DEPOSIT')->first();

    expect($transactionType)->not->toBeNull()
        ->and($transactionType->name)->toBe('Setoran Tabungan Siswa')
        ->and($transactionType->category)->toBe(TransactionCategory::Liability);

    $liabilityConfig = $transactionType->configs()->where('config_key', 'liability_credit')->first();
    $cashConfig = $transactionType->configs()->where('config_key', 'cash_debit')->first();

    expect($liabilityConfig->account->code)->toBe('2-1103')
        ->and($cashConfig->account_id)->toBeNull(); // Dynamic user selection
});

it('creates salary payroll transaction type with mapped accounts', function () {
    $transactionType = TransactionType::where('code', 'SALARY_PAYROLL')->first();

    expect($transactionType)->not->toBeNull()
        ->and($transactionType->name)->toBe('Penggajian Guru & Staf')
        ->and($transactionType->category)->toBe(TransactionCategory::Payroll);

    $salaryConfig = $transactionType->configs()->where('config_key', 'salary_expense_debit')->first();
    $taxConfig = $transactionType->configs()->where('config_key', 'tax_payable_credit')->first();

    expect($salaryConfig->account->code)->toBe('6-1101')
        ->and($taxConfig->account->code)->toBe('2-1105');
});

it('creates utility payment transaction type with mapped expense account', function () {
    $transactionType = TransactionType::where('code', 'UTILITY_PAYMENT')->first();

    expect($transactionType)->not->toBeNull()
        ->and($transactionType->name)->toBe('Pembayaran Listrik, Air & Internet')
        ->and($transactionType->category)->toBe(TransactionCategory::Expense);

    $expenseConfig = $transactionType->configs()->where('config_key', 'expense_debit')->first();

    expect($expenseConfig->account->code)->toBe('6-1103');
});

it('creates asset depreciation transaction type with mapped accounts', function () {
    $transactionType = TransactionType::where('code', 'ASSET_DEPRECIATION')->first();

    expect($transactionType)->not->toBeNull()
        ->and($transactionType->name)->toBe('Penyusutan Aset Tetap')
        ->and($transactionType->category)->toBe(TransactionCategory::Asset);

    $depreciationConfig = $transactionType->configs()->where('config_key', 'depreciation_expense_debit')->first();
    $accumulatedConfig = $transactionType->configs()->where('config_key', 'accumulated_depreciation_credit')->first();

    expect($depreciationConfig->account->code)->toBe('6-1107')
        ->and($accumulatedConfig->account->code)->toBe('1-2103');
});

it('creates inventory usage transaction type with mapped accounts', function () {
    $transactionType = TransactionType::where('code', 'INVENTORY_USAGE')->first();

    expect($transactionType)->not->toBeNull()
        ->and($transactionType->name)->toBe('Pemakaian Perlengkapan/ATK')
        ->and($transactionType->category)->toBe(TransactionCategory::Expense);

    $expenseConfig = $transactionType->configs()->where('config_key', 'expense_debit')->first();
    $inventoryConfig = $transactionType->configs()->where('config_key', 'inventory_credit')->first();

    expect($expenseConfig->account->code)->toBe('6-1108')
        ->and($inventoryConfig->account->code)->toBe('1-1104');
});

it('creates late fine transaction type with mapped accounts', function () {
    $transactionType = TransactionType::where('code', 'LATE_FINE')->first();

    expect($transactionType)->not->toBeNull()
        ->and($transactionType->name)->toBe('Denda Keterlambatan Pembayaran');

    $receivableConfig = $transactionType->configs()->where('config_key', 'receivable_debit')->first();
    $fineConfig = $transactionType->configs()->where('config_key', 'fine_revenue_credit')->first();

    expect($receivableConfig->account->code)->toBe('1-1103')
        ->and($fineConfig->account->code)->toBe('4-1103');
});

it('ensures all transaction types have appropriate entry configs', function () {
    $transactionTypes = TransactionType::with('configs')->get();

    expect($transactionTypes)->toHaveCount(10);

    foreach ($transactionTypes as $transactionType) {
        expect($transactionType->configs)->not->toBeEmpty()
            ->and($transactionType->is_system)->toBeTrue()
            ->and($transactionType->is_active)->toBeTrue();
    }
});

it('can re-run seeders without creating duplicates', function () {
    $initialAccountCount = ChartOfAccount::count();
    $initialTransactionTypeCount = TransactionType::count();

    // Re-run seeders
    $this->seed(ChartOfAccountSeeder::class);
    $this->seed(TransactionTypeSeeder::class);

    expect(ChartOfAccount::count())->toBe($initialAccountCount)
        ->and(TransactionType::count())->toBe($initialTransactionTypeCount);
});

<?php

namespace Database\Seeders\Finance;

use App\Enums\Finance\EntryPosition;
use App\Enums\Finance\TransactionCategory;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\TransactionEntryConfig;
use App\Models\Finance\TransactionType;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedTuitionBilling();
        $this->seedTuitionPayment();
        $this->seedAdmissionRegistration();
        $this->seedUniformSales();
        $this->seedStudentSavingDeposit();
        $this->seedSalaryPayroll();
        $this->seedUtilityPayment();
        $this->seedAssetDepreciation();
        $this->seedInventoryUsage();
        $this->seedLateFine();
    }

    /**
     * 1. TUITION_BILLING - Tagihan SPP Bulanan
     */
    private function seedTuitionBilling(): void
    {
        $transactionType = TransactionType::query()->updateOrCreate(
            ['code' => 'TUITION_BILLING'],
            [
                'is_system' => true,
                'name' => 'Tagihan SPP Bulanan',
                'category' => TransactionCategory::Billing,
                'is_active' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'receivable_debit'],
            [
                'ui_label' => 'Akun Piutang Siswa',
                'position' => EntryPosition::Debit,
                'account_type_filter' => 'ASSET',
                'account_id' => $this->getAccountId('1-1103'), // Piutang Siswa
                'is_required' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'revenue_credit'],
            [
                'ui_label' => 'Akun Pendapatan SPP',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'REVENUE',
                'account_id' => $this->getAccountId('4-1101'), // Pendapatan SPP
                'is_required' => true,
            ]
        );
    }

    /**
     * 2. TUITION_PAYMENT - Pembayaran SPP
     */
    private function seedTuitionPayment(): void
    {
        $transactionType = TransactionType::query()->updateOrCreate(
            ['code' => 'TUITION_PAYMENT'],
            [
                'is_system' => true,
                'name' => 'Pembayaran SPP',
                'category' => TransactionCategory::Income,
                'is_active' => true,
            ]
        );

        // Dynamic debit side (Cash/Bank) - user selects at runtime
        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'cash_debit'],
            [
                'ui_label' => 'Akun Kas/Bank',
                'position' => EntryPosition::Debit,
                'account_type_filter' => 'ASSET',
                'account_id' => null, // User selects cash/bank account
                'is_required' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'receivable_credit'],
            [
                'ui_label' => 'Akun Piutang Siswa',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'ASSET',
                'account_id' => $this->getAccountId('1-1103'), // Piutang Siswa
                'is_required' => true,
            ]
        );
    }

    /**
     * 3. ADMISSION_REGISTRATION - Pendaftaran PPDB
     */
    private function seedAdmissionRegistration(): void
    {
        $transactionType = TransactionType::query()->updateOrCreate(
            ['code' => 'ADMISSION_REGISTRATION'],
            [
                'is_system' => true,
                'name' => 'Pendaftaran PPDB (Uang Pangkal)',
                'category' => TransactionCategory::Billing,
                'is_active' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'receivable_debit'],
            [
                'ui_label' => 'Akun Piutang Siswa',
                'position' => EntryPosition::Debit,
                'account_type_filter' => 'ASSET',
                'account_id' => $this->getAccountId('1-1103'), // Piutang Siswa
                'is_required' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'revenue_credit'],
            [
                'ui_label' => 'Akun Pendapatan Uang Pangkal',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'REVENUE',
                'account_id' => $this->getAccountId('4-1102'), // Pendapatan Uang Pangkal
                'is_required' => true,
            ]
        );
    }

    /**
     * 4. UNIFORM_SALES - Penjualan Seragam & Buku
     */
    private function seedUniformSales(): void
    {
        $transactionType = TransactionType::query()->updateOrCreate(
            ['code' => 'UNIFORM_SALES'],
            [
                'is_system' => true,
                'name' => 'Penjualan Seragam & Buku',
                'category' => TransactionCategory::Income,
                'is_active' => true,
            ]
        );

        // Dynamic debit side (Cash/Bank) - user selects at runtime
        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'cash_debit'],
            [
                'ui_label' => 'Akun Kas/Bank',
                'position' => EntryPosition::Debit,
                'account_type_filter' => 'ASSET',
                'account_id' => null, // User selects cash/bank account
                'is_required' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'revenue_credit'],
            [
                'ui_label' => 'Pendapatan Seragam & Buku',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'REVENUE',
                'account_id' => $this->getAccountId('4-1104'), // Penjualan Seragam & Buku
                'is_required' => true,
            ]
        );
    }

    /**
     * 5. STUDENT_SAVING_DEPOSIT - Setoran Tabungan Siswa
     */
    private function seedStudentSavingDeposit(): void
    {
        $transactionType = TransactionType::query()->updateOrCreate(
            ['code' => 'STUDENT_SAVING_DEPOSIT'],
            [
                'is_system' => true,
                'name' => 'Setoran Tabungan Siswa',
                'category' => TransactionCategory::Liability,
                'is_active' => true,
            ]
        );

        // Dynamic debit side (Cash/Bank) - user selects at runtime
        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'cash_debit'],
            [
                'ui_label' => 'Akun Kas/Bank',
                'position' => EntryPosition::Debit,
                'account_type_filter' => 'ASSET',
                'account_id' => null, // User selects cash/bank account
                'is_required' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'liability_credit'],
            [
                'ui_label' => 'Tabungan Siswa',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'LIABILITY',
                'account_id' => $this->getAccountId('2-1103'), // Tabungan Siswa
                'is_required' => true,
            ]
        );
    }

    /**
     * 6. SALARY_PAYROLL - Gaji Guru & Staf
     */
    private function seedSalaryPayroll(): void
    {
        $transactionType = TransactionType::query()->updateOrCreate(
            ['code' => 'SALARY_PAYROLL'],
            [
                'is_system' => true,
                'name' => 'Penggajian Guru & Staf',
                'category' => TransactionCategory::Payroll,
                'is_active' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'salary_expense_debit'],
            [
                'ui_label' => 'Beban Gaji Guru',
                'position' => EntryPosition::Debit,
                'account_type_filter' => 'EXPENSE',
                'account_id' => $this->getAccountId('6-1101'), // Beban Gaji Guru
                'is_required' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'tax_payable_credit'],
            [
                'ui_label' => 'Utang PPh 21',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'LIABILITY',
                'account_id' => $this->getAccountId('2-1105'), // Utang PPh 21
                'is_required' => false,
            ]
        );

        // Dynamic credit side (Cash/Bank) for net salary payment
        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'cash_credit'],
            [
                'ui_label' => 'Akun Kas/Bank (Gaji Bersih)',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'ASSET',
                'account_id' => null, // User selects cash/bank account
                'is_required' => true,
            ]
        );
    }

    /**
     * 7. UTILITY_PAYMENT - Listrik, Air & Internet
     */
    private function seedUtilityPayment(): void
    {
        $transactionType = TransactionType::query()->updateOrCreate(
            ['code' => 'UTILITY_PAYMENT'],
            [
                'is_system' => true,
                'name' => 'Pembayaran Listrik, Air & Internet',
                'category' => TransactionCategory::Expense,
                'is_active' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'expense_debit'],
            [
                'ui_label' => 'Beban Listrik, Air & Internet',
                'position' => EntryPosition::Debit,
                'account_type_filter' => 'EXPENSE',
                'account_id' => $this->getAccountId('6-1103'), // Beban Listrik, Air & Internet
                'is_required' => true,
            ]
        );

        // Dynamic credit side (Cash/Bank) - user selects at runtime
        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'cash_credit'],
            [
                'ui_label' => 'Akun Kas/Bank',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'ASSET',
                'account_id' => null, // User selects cash/bank account
                'is_required' => true,
            ]
        );
    }

    /**
     * 8. ASSET_DEPRECIATION - Penyusutan Aset
     */
    private function seedAssetDepreciation(): void
    {
        $transactionType = TransactionType::query()->updateOrCreate(
            ['code' => 'ASSET_DEPRECIATION'],
            [
                'is_system' => true,
                'name' => 'Penyusutan Aset Tetap',
                'category' => TransactionCategory::Asset,
                'is_active' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'depreciation_expense_debit'],
            [
                'ui_label' => 'Beban Penyusutan Aset',
                'position' => EntryPosition::Debit,
                'account_type_filter' => 'EXPENSE',
                'account_id' => $this->getAccountId('6-1107'), // Beban Penyusutan Aset
                'is_required' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'accumulated_depreciation_credit'],
            [
                'ui_label' => 'Akumulasi Penyusutan',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'ASSET',
                'account_id' => $this->getAccountId('1-2103'), // Akumulasi Penyusutan
                'is_required' => true,
            ]
        );
    }

    /**
     * 9. INVENTORY_USAGE - Pemakaian ATK/Perlengkapan
     */
    private function seedInventoryUsage(): void
    {
        $transactionType = TransactionType::query()->updateOrCreate(
            ['code' => 'INVENTORY_USAGE'],
            [
                'is_system' => true,
                'name' => 'Pemakaian Perlengkapan/ATK',
                'category' => TransactionCategory::Expense,
                'is_active' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'expense_debit'],
            [
                'ui_label' => 'Beban Perlengkapan/ATK',
                'position' => EntryPosition::Debit,
                'account_type_filter' => 'EXPENSE',
                'account_id' => $this->getAccountId('6-1108'), // Beban Perlengkapan/ATK
                'is_required' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'inventory_credit'],
            [
                'ui_label' => 'Perlengkapan/Inventory',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'ASSET',
                'account_id' => $this->getAccountId('1-1104'), // Perlengkapan/Inventory
                'is_required' => true,
            ]
        );
    }

    /**
     * 10. LATE_FINE - Denda Keterlambatan
     */
    private function seedLateFine(): void
    {
        $transactionType = TransactionType::query()->updateOrCreate(
            ['code' => 'LATE_FINE'],
            [
                'is_system' => true,
                'name' => 'Denda Keterlambatan Pembayaran',
                'category' => TransactionCategory::Income,
                'is_active' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'receivable_debit'],
            [
                'ui_label' => 'Piutang Denda',
                'position' => EntryPosition::Debit,
                'account_type_filter' => 'ASSET',
                'account_id' => $this->getAccountId('1-1103'), // Piutang Siswa
                'is_required' => true,
            ]
        );

        TransactionEntryConfig::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'config_key' => 'fine_revenue_credit'],
            [
                'ui_label' => 'Pendapatan Denda',
                'position' => EntryPosition::Credit,
                'account_type_filter' => 'REVENUE',
                'account_id' => $this->getAccountId('4-1103'), // Pendapatan Denda
                'is_required' => true,
            ]
        );
    }

    /**
     * Helper method to get account ID by code.
     * Returns null if account is not found.
     */
    private function getAccountId(string $code): ?string
    {
        return ChartOfAccount::query()
            ->where('code', $code)
            ->first()
            ?->id;
    }
}

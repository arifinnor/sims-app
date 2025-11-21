<?php

namespace Database\Seeders\Finance;

use App\Enums\Finance\AccountType;
use App\Enums\Finance\EntryPosition;
use App\Enums\Finance\TransactionCategory;
use App\Models\Finance\ChartOfAccount;
use App\Models\Finance\TransactionAccount;
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

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'receivable_debit'],
            [
                'label' => 'Akun Piutang Siswa',
                'direction' => EntryPosition::Debit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => $this->getAccountId('1-1103'), // Piutang Siswa
            ]
        );

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'revenue_credit'],
            [
                'label' => 'Akun Pendapatan SPP',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Revenue,
                'chart_of_account_id' => $this->getAccountId('4-1101'), // Pendapatan SPP
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
        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'cash_debit'],
            [
                'label' => 'Akun Kas/Bank',
                'direction' => EntryPosition::Debit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => null, // User selects cash/bank account
            ]
        );

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'receivable_credit'],
            [
                'label' => 'Akun Piutang Siswa',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => $this->getAccountId('1-1103'), // Piutang Siswa
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

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'receivable_debit'],
            [
                'label' => 'Akun Piutang Siswa',
                'direction' => EntryPosition::Debit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => $this->getAccountId('1-1103'), // Piutang Siswa
            ]
        );

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'revenue_credit'],
            [
                'label' => 'Akun Pendapatan Uang Pangkal',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Revenue,
                'chart_of_account_id' => $this->getAccountId('4-1102'), // Pendapatan Uang Pangkal
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
        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'cash_debit'],
            [
                'label' => 'Akun Kas/Bank',
                'direction' => EntryPosition::Debit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => null, // User selects cash/bank account
            ]
        );

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'revenue_credit'],
            [
                'label' => 'Pendapatan Seragam & Buku',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Revenue,
                'chart_of_account_id' => $this->getAccountId('4-1104'), // Penjualan Seragam & Buku
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
        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'cash_debit'],
            [
                'label' => 'Akun Kas/Bank',
                'direction' => EntryPosition::Debit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => null, // User selects cash/bank account
            ]
        );

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'liability_credit'],
            [
                'label' => 'Tabungan Siswa',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Liability,
                'chart_of_account_id' => $this->getAccountId('2-1103'), // Tabungan Siswa
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

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'salary_expense_debit'],
            [
                'label' => 'Beban Gaji Guru',
                'direction' => EntryPosition::Debit,
                'account_type' => AccountType::Expense,
                'chart_of_account_id' => $this->getAccountId('6-1101'), // Beban Gaji Guru
            ]
        );

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'tax_payable_credit'],
            [
                'label' => 'Utang PPh 21',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Liability,
                'chart_of_account_id' => $this->getAccountId('2-1105'), // Utang PPh 21
            ]
        );

        // Dynamic credit side (Cash/Bank) for net salary payment
        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'cash_credit'],
            [
                'label' => 'Akun Kas/Bank (Gaji Bersih)',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => null, // User selects cash/bank account
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

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'expense_debit'],
            [
                'label' => 'Beban Listrik, Air & Internet',
                'direction' => EntryPosition::Debit,
                'account_type' => AccountType::Expense,
                'chart_of_account_id' => $this->getAccountId('6-1103'), // Beban Listrik, Air & Internet
            ]
        );

        // Dynamic credit side (Cash/Bank) - user selects at runtime
        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'cash_credit'],
            [
                'label' => 'Akun Kas/Bank',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => null, // User selects cash/bank account
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

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'depreciation_expense_debit'],
            [
                'label' => 'Beban Penyusutan Aset',
                'direction' => EntryPosition::Debit,
                'account_type' => AccountType::Expense,
                'chart_of_account_id' => $this->getAccountId('6-1107'), // Beban Penyusutan Aset
            ]
        );

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'accumulated_depreciation_credit'],
            [
                'label' => 'Akumulasi Penyusutan',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => $this->getAccountId('1-2103'), // Akumulasi Penyusutan
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

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'expense_debit'],
            [
                'label' => 'Beban Perlengkapan/ATK',
                'direction' => EntryPosition::Debit,
                'account_type' => AccountType::Expense,
                'chart_of_account_id' => $this->getAccountId('6-1108'), // Beban Perlengkapan/ATK
            ]
        );

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'inventory_credit'],
            [
                'label' => 'Perlengkapan/Inventory',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => $this->getAccountId('1-1104'), // Perlengkapan/Inventory
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

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'receivable_debit'],
            [
                'label' => 'Piutang Denda',
                'direction' => EntryPosition::Debit,
                'account_type' => AccountType::Asset,
                'chart_of_account_id' => $this->getAccountId('1-1103'), // Piutang Siswa
            ]
        );

        TransactionAccount::query()->updateOrCreate(
            ['transaction_type_id' => $transactionType->id, 'role' => 'fine_revenue_credit'],
            [
                'label' => 'Pendapatan Denda',
                'direction' => EntryPosition::Credit,
                'account_type' => AccountType::Revenue,
                'chart_of_account_id' => $this->getAccountId('4-1103'), // Pendapatan Denda
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

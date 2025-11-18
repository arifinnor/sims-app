<?php

namespace Database\Seeders\Finance;

use App\Enums\Finance\AccountType;
use App\Models\Finance\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assets (1000)
        $assets = Account::create([
            'account_number' => '1000',
            'name' => 'Assets',
            'type' => AccountType::Asset,
            'category' => null,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        // Current Assets (1010)
        $currentAssets = Account::create([
            'account_number' => '1010',
            'name' => 'Current Assets',
            'type' => AccountType::Asset,
            'category' => 'current_asset',
            'parent_account_id' => $assets->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '1011',
            'name' => 'Cash',
            'type' => AccountType::Asset,
            'category' => 'current_asset',
            'parent_account_id' => $currentAssets->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '1012',
            'name' => 'Bank Accounts',
            'type' => AccountType::Asset,
            'category' => 'current_asset',
            'parent_account_id' => $currentAssets->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '1013',
            'name' => 'Accounts Receivable',
            'type' => AccountType::Asset,
            'category' => 'current_asset',
            'parent_account_id' => $currentAssets->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        // Fixed Assets (1020)
        $fixedAssets = Account::create([
            'account_number' => '1020',
            'name' => 'Fixed Assets',
            'type' => AccountType::Asset,
            'category' => 'fixed_asset',
            'parent_account_id' => $assets->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '1021',
            'name' => 'Buildings',
            'type' => AccountType::Asset,
            'category' => 'fixed_asset',
            'parent_account_id' => $fixedAssets->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '1022',
            'name' => 'Equipment',
            'type' => AccountType::Asset,
            'category' => 'fixed_asset',
            'parent_account_id' => $fixedAssets->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        // Liabilities (2000)
        $liabilities = Account::create([
            'account_number' => '2000',
            'name' => 'Liabilities',
            'type' => AccountType::Liability,
            'category' => null,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        // Current Liabilities (2010)
        $currentLiabilities = Account::create([
            'account_number' => '2010',
            'name' => 'Current Liabilities',
            'type' => AccountType::Liability,
            'category' => 'current_liability',
            'parent_account_id' => $liabilities->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '2011',
            'name' => 'Accounts Payable',
            'type' => AccountType::Liability,
            'category' => 'current_liability',
            'parent_account_id' => $currentLiabilities->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '2012',
            'name' => 'Accrued Expenses',
            'type' => AccountType::Liability,
            'category' => 'current_liability',
            'parent_account_id' => $currentLiabilities->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        // Equity (3000)
        $equity = Account::create([
            'account_number' => '3000',
            'name' => 'Equity',
            'type' => AccountType::Equity,
            'category' => null,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '3010',
            'name' => 'Capital',
            'type' => AccountType::Equity,
            'category' => 'capital',
            'parent_account_id' => $equity->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '3020',
            'name' => 'Retained Earnings',
            'type' => AccountType::Equity,
            'category' => 'retained_earnings',
            'parent_account_id' => $equity->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        // Revenue (4000)
        $revenue = Account::create([
            'account_number' => '4000',
            'name' => 'Revenue',
            'type' => AccountType::Revenue,
            'category' => null,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        // Operating Revenue (4010)
        $operatingRevenue = Account::create([
            'account_number' => '4010',
            'name' => 'Operating Revenue',
            'type' => AccountType::Revenue,
            'category' => 'operating_revenue',
            'parent_account_id' => $revenue->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '4011',
            'name' => 'Tuition Revenue',
            'type' => AccountType::Revenue,
            'category' => 'operating_revenue',
            'parent_account_id' => $operatingRevenue->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '4012',
            'name' => 'Registration Fees',
            'type' => AccountType::Revenue,
            'category' => 'operating_revenue',
            'parent_account_id' => $operatingRevenue->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        // Expenses (5000)
        $expenses = Account::create([
            'account_number' => '5000',
            'name' => 'Expenses',
            'type' => AccountType::Expense,
            'category' => null,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        // Operating Expenses (5010)
        $operatingExpenses = Account::create([
            'account_number' => '5010',
            'name' => 'Operating Expenses',
            'type' => AccountType::Expense,
            'category' => 'operating_expense',
            'parent_account_id' => $expenses->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '5011',
            'name' => 'Salaries and Wages',
            'type' => AccountType::Expense,
            'category' => 'operating_expense',
            'parent_account_id' => $operatingExpenses->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '5012',
            'name' => 'Rent',
            'type' => AccountType::Expense,
            'category' => 'operating_expense',
            'parent_account_id' => $operatingExpenses->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '5013',
            'name' => 'Utilities',
            'type' => AccountType::Expense,
            'category' => 'operating_expense',
            'parent_account_id' => $operatingExpenses->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        Account::create([
            'account_number' => '5014',
            'name' => 'Supplies',
            'type' => AccountType::Expense,
            'category' => 'operating_expense',
            'parent_account_id' => $operatingExpenses->id,
            'balance' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);
    }
}

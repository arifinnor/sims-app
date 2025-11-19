<?php

namespace Database\Seeders\Finance;

use App\Enums\Finance\AccountType;
use App\Enums\Finance\NormalBalance;
use App\Models\Finance\AccountCategory;
use App\Models\Finance\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = AccountCategory::query()
            ->get(['id', 'name'])
            ->keyBy('name')
            ->map(fn (AccountCategory $category) => $category->getKey());

        $accounts = [];

        $definitions = [
            ['code' => '1-0000', 'name' => 'Assets', 'category' => 'Current Assets', 'type' => AccountType::Asset, 'is_posting' => false],
            ['code' => '1-1000', 'name' => 'Cash and Cash Equivalents', 'category' => 'Current Assets', 'type' => AccountType::Asset, 'parent' => '1-0000', 'is_cash' => true],
            ['code' => '1-1100', 'name' => 'Petty Cash', 'category' => 'Current Assets', 'type' => AccountType::Asset, 'parent' => '1-1000', 'is_cash' => true],
            ['code' => '1-1200', 'name' => 'Operating Bank Account', 'category' => 'Current Assets', 'type' => AccountType::Asset, 'parent' => '1-1000', 'is_cash' => true],
            ['code' => '1-2000', 'name' => 'Accounts Receivable', 'category' => 'Current Assets', 'type' => AccountType::Asset, 'parent' => '1-0000'],
            ['code' => '1-3000', 'name' => 'Prepaid Expenses', 'category' => 'Current Assets', 'type' => AccountType::Asset, 'parent' => '1-0000'],
            ['code' => '1-4000', 'name' => 'Property and Equipment', 'category' => 'Non-Current Assets', 'type' => AccountType::Asset, 'parent' => '1-0000'],

            ['code' => '2-0000', 'name' => 'Liabilities', 'category' => 'Current Liabilities', 'type' => AccountType::Liability, 'is_posting' => false],
            ['code' => '2-1000', 'name' => 'Accounts Payable', 'category' => 'Current Liabilities', 'type' => AccountType::Liability, 'parent' => '2-0000'],
            ['code' => '2-2000', 'name' => 'Accrued Expenses', 'category' => 'Current Liabilities', 'type' => AccountType::Liability, 'parent' => '2-0000'],
            ['code' => '2-3000', 'name' => 'Deferred Revenue', 'category' => 'Current Liabilities', 'type' => AccountType::Liability, 'parent' => '2-0000'],

            ['code' => '3-0000', 'name' => 'Equity', 'category' => 'Equity', 'type' => AccountType::Equity, 'is_posting' => false],
            ['code' => '3-1000', 'name' => 'Retained Earnings', 'category' => 'Equity', 'type' => AccountType::Equity, 'parent' => '3-0000'],
            ['code' => '3-2000', 'name' => 'Capital Contributions', 'category' => 'Equity', 'type' => AccountType::Equity, 'parent' => '3-0000'],

            ['code' => '4-0000', 'name' => 'Revenue', 'category' => 'Operating Revenue', 'type' => AccountType::Revenue, 'is_posting' => false],
            ['code' => '4-1000', 'name' => 'Tuition Revenue', 'category' => 'Operating Revenue', 'type' => AccountType::Revenue, 'parent' => '4-0000'],
            ['code' => '4-2000', 'name' => 'Grants and Subsidies', 'category' => 'Non-Operating Revenue', 'type' => AccountType::Revenue, 'parent' => '4-0000'],

            ['code' => '5-0000', 'name' => 'Expenses', 'category' => 'Operating Expenses', 'type' => AccountType::Expense, 'is_posting' => false],
            ['code' => '5-1000', 'name' => 'Salaries Expense', 'category' => 'Operating Expenses', 'type' => AccountType::Expense, 'parent' => '5-0000'],
            ['code' => '5-1100', 'name' => 'Benefits Expense', 'category' => 'Operating Expenses', 'type' => AccountType::Expense, 'parent' => '5-0000'],
            ['code' => '5-2000', 'name' => 'Rent Expense', 'category' => 'Operating Expenses', 'type' => AccountType::Expense, 'parent' => '5-0000'],
            ['code' => '5-3000', 'name' => 'Supplies Expense', 'category' => 'Operating Expenses', 'type' => AccountType::Expense, 'parent' => '5-0000'],
            ['code' => '5-4000', 'name' => 'Scholarship Expense', 'category' => 'Cost of Goods Sold', 'type' => AccountType::Expense, 'parent' => '5-0000'],
        ];

        foreach ($definitions as $definition) {
            $parentId = null;
            if (isset($definition['parent'])) {
                $parent = $accounts[$definition['parent']] ?? ChartOfAccount::query()->where('code', $definition['parent'])->first();
                $parentId = $parent?->getKey();
            }

            $payload = [
                'category_id' => $categories[$definition['category']] ?? null,
                'name' => $definition['name'],
                'description' => $definition['description'] ?? null,
                'parent_id' => $parentId,
                'account_type' => $definition['type']->value,
                'normal_balance' => $this->normalBalanceFor($definition['type'])->value,
                'is_posting' => $definition['is_posting'] ?? true,
                'is_cash' => $definition['is_cash'] ?? false,
                'is_active' => $definition['is_active'] ?? true,
            ];

            $account = ChartOfAccount::query()->updateOrCreate(
                ['code' => $definition['code']],
                $payload
            );

            $accounts[$definition['code']] = $account;
        }
    }

    private function normalBalanceFor(AccountType $type): NormalBalance
    {
        return match ($type) {
            AccountType::Asset,
            AccountType::Expense => NormalBalance::Debit,
            default => NormalBalance::Credit,
        };
    }
}

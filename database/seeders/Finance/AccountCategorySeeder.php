<?php

namespace Database\Seeders\Finance;

use App\Enums\Finance\ReportType;
use App\Models\Finance\AccountCategory;
use Illuminate\Database\Seeder;

class AccountCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Current Assets', 'report_type' => ReportType::BalanceSheet->value, 'sequence' => 100],
            ['name' => 'Non-Current Assets', 'report_type' => ReportType::BalanceSheet->value, 'sequence' => 200],
            ['name' => 'Current Liabilities', 'report_type' => ReportType::BalanceSheet->value, 'sequence' => 300],
            ['name' => 'Non-Current Liabilities', 'report_type' => ReportType::BalanceSheet->value, 'sequence' => 400],
            ['name' => 'Equity', 'report_type' => ReportType::BalanceSheet->value, 'sequence' => 500],
            ['name' => 'Operating Revenue', 'report_type' => ReportType::IncomeStatement->value, 'sequence' => 600],
            ['name' => 'Non-Operating Revenue', 'report_type' => ReportType::IncomeStatement->value, 'sequence' => 650],
            ['name' => 'Cost of Goods Sold', 'report_type' => ReportType::IncomeStatement->value, 'sequence' => 700],
            ['name' => 'Operating Expenses', 'report_type' => ReportType::IncomeStatement->value, 'sequence' => 800],
            ['name' => 'Non-Operating Expenses', 'report_type' => ReportType::IncomeStatement->value, 'sequence' => 900],
        ];

        foreach ($categories as $category) {
            AccountCategory::query()->updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}

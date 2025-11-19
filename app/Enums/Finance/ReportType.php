<?php

namespace App\Enums\Finance;

enum ReportType: string
{
    case BalanceSheet = 'BALANCE_SHEET';
    case IncomeStatement = 'INCOME_STATEMENT';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

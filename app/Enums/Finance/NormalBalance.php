<?php

namespace App\Enums\Finance;

enum NormalBalance: string
{
    case Debit = 'DEBIT';
    case Credit = 'CREDIT';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

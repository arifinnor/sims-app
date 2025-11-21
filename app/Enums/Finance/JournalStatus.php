<?php

namespace App\Enums\Finance;

enum JournalStatus: string
{
    case Draft = 'DRAFT';
    case Posted = 'POSTED';
    case Void = 'VOID';

    /**
     * Get all enum values as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

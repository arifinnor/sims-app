<?php

namespace App\Enums\Academic;

enum EnrollmentStatus: string
{
    case Active = 'ACTIVE';
    case Moved = 'MOVED';
    case Dropped = 'DROPPED';

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

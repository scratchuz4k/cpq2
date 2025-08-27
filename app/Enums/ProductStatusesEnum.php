<?php

namespace App\Enums;

enum ProductStatusesEnum: string
{
    case RETIRED = 'Retired';
    case ACTIVE = 'Active';
    case INACTIVE = 'Inactive';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}

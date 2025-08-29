<?php

namespace App\Enums;

use App\Trait\EnumHelpers;

enum ProductStatusesEnum: string
{
    use EnumHelpers;

    case RETIRED = 'Retired';
    case ACTIVE = 'Active';
    case INACTIVE = 'Inactive';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}

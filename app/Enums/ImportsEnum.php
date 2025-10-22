<?php

namespace App\Enums;

use App\Imports\ProductsImport;
use App\Imports\UsersImport;
use App\Trait\EnumHelpers;

enum ImportsEnum: string
{
    use EnumHelpers;

    case Product = ProductsImport::class;
    case User = UsersImport::class;

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}

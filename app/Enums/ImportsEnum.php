<?php

namespace App\Enums;

use App\Imports\Import;
use App\Imports\ProductsImport;
use App\Imports\UsersImport;
use App\Trait\EnumHelpers;

enum ImportsEnum: string
{
    use EnumHelpers;

    case Product = 'product';
    case User = 'user';
    case Category = 'category';
    case Step = 'step';
    case Supplier = 'supplier';
    case Account = 'account';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public function importClass(): string
    {
        return match ($this) {
            self::Product => ProductsImport::class,
            self::User => UsersImport::class,
            default => Import::class,
        };
    }
}

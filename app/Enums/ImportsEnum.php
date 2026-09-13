<?php

namespace App\Enums;

use App\Imports\Import;
use App\Imports\ProductsImport;
use App\Imports\UsersImport;
use App\Models\Account;
use App\Models\Category;
use App\Models\Product;
use App\Models\Step;
use App\Models\Supplier;
use App\Models\User;
use App\Trait\EnumHelpers;

enum ImportsEnum: string
{
    use EnumHelpers;

    case Product = 'products';
    case User = 'users';
    case Category = 'categories';
    case Step = 'steps';
    case Supplier = 'suppliers';
    case Account = 'accounts';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public function importClasses(): string
    {
        return match ($this) {
            self::Product => ProductsImport::class,
            self::User => UsersImport::class,
            default => Import::class,
        };
    }

    public static function importClassFor(string $modelName): string
    {
        $case = collect(self::cases())->firstWhere('name', $modelName);

        return $case?->importClasses() ?? Import::class;
    }

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], self::cases());
    }
}

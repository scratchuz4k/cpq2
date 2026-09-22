<?php

namespace App\Enums;

use App\Imports\Import;
use App\Imports\ProductAddonImport;
use App\Imports\ProductBundleImport;
use App\Imports\ProductLinkImport;
use App\Imports\ProductsImport;
use App\Imports\UsersImport;
use App\Trait\EnumHelpers;

enum ImportsEnum: string
{
    use EnumHelpers;

    case Product = 'products';
    case User = 'users';
    case Category = 'categories';
    case Step = 'steps';
    case Account = 'accounts';
    case ProductLink = 'product-links';
    case ProductAddon = 'product-addons';
    case ProductBundle = 'product-bundles';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public function importClasses(): string
    {
        return match ($this) {
            self::Product => ProductsImport::class,
            self::User => UsersImport::class,
            self::ProductLink => ProductLinkImport::class,
            self::ProductAddon => ProductAddonImport::class,
            self::ProductBundle => ProductBundleImport::class,
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
        return self::asArrayFor(self::cases());
    }

    /**
     * @param  self[]  $cases
     */
    public static function asArrayFor(array $cases): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], $cases);
    }

    public static function importsWithPermissionAsArray(): array
    {
        $user = auth()->user();

        $cases = array_filter(self::cases(), function ($case) use ($user) {
            return $user->can('create import') && $user->can('import '.$case->permissionGroup());
        });

        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], array_values($cases));
    }

    /**
     * The permission group each case is gated behind. Product relationship
     * imports ride on the "product" permission, matching ProductLinkPolicy,
     * ProductAddonPolicy and ProductBundlePolicy's `import()` checks.
     */
    private function permissionGroup(): string
    {
        return match ($this) {
            self::ProductLink, self::ProductAddon, self::ProductBundle => 'product',
            default => strtolower($this->name),
        };
    }
}

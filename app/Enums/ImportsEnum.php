<?php

namespace App\Enums;

use App\Imports\AccountsImport;
use App\Imports\CategoriesImport;
use App\Imports\ContactsImport;
use App\Imports\Import;
use App\Imports\ProductAddonImport;
use App\Imports\ProductBundleImport;
use App\Imports\ProductLinkImport;
use App\Imports\ProductsImport;
use App\Imports\StepsImport;
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
    case Contact = 'contacts';
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
            self::Account => AccountsImport::class,
            self::Contact => ContactsImport::class,
            self::ProductLink => ProductLinkImport::class,
            self::ProductAddon => ProductAddonImport::class,
            self::ProductBundle => ProductBundleImport::class,
            self::Category => CategoriesImport::class,
            self::Step => StepsImport::class,
            default => Import::class,
        };
    }

    /**
     * The label shown for this import type in the import dropdowns.
     */
    public function label(): string
    {
        return match ($this) {
            self::Contact => 'Account Contact',
            self::ProductLink => 'Product Link',
            self::ProductAddon => 'Product Addon',
            self::ProductBundle => 'Product Bundle',
            default => $this->name,
        };
    }

    /**
     * Whether this import type supports update
     */
    public function canOverwrite(): bool
    {
        return match ($this) {
            self::Account,
            self::Category,
            self::Product,
            self::ProductBundle,
            self::User => true,
            default => false,
        };
    }

    public static function forModel(string $modelName): ?self
    {
        return collect(self::cases())->firstWhere('name', $modelName);
    }

    public static function importClassFor(string $modelName): string
    {
        return self::forModel($modelName)?->importClasses() ?? Import::class;
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
        $items = array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
            'label' => $case->label(),
            'canOverwrite' => $case->canOverwrite(),
        ], $cases);

        usort($items, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

        return $items;
    }

    public static function importsWithPermissionAsArray(): array
    {
        $user = auth()->user();

        $cases = array_filter(self::cases(), function ($case) use ($user) {
            return $user->can('create import') && $user->can('import '.$case->permissionGroup());
        });

        return self::asArrayFor(array_values($cases));
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

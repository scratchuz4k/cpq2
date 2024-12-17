<?php

namespace App\Enums;

use App\Models\Tenant;
use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use App\Helpers\Prices\Rules\RuleGlobal;
use App\Helpers\Prices\Rules\RuleAccount;
use App\Helpers\Prices\Rules\RuleProduct;
use App\Helpers\Prices\Rules\RuleSupplier;
use App\Helpers\Prices\Rules\RuleAccountProduct;
use App\Helpers\Prices\Rules\RuleAccountSupplier;
use App\Helpers\Prices\Rules\RuleSupplierProduct;

enum SpaRulesEnum: string
{
    case Global = RuleGlobal::class;
    case AccountProduct = RuleAccountProduct::class;
    case AccountSupplier = RuleAccountSupplier::class;
    case SupplierProduct = RuleSupplierProduct::class;
    case Account = RuleAccount::class;
    case Supplier = RuleSupplier::class;
    case Product = RuleProduct::class;

    public function getTasks(): array
    {
        return match ($this) {
            SpaRulesEnum::AccountProduct => ['entity_1' => Account::class, 'entity_2' => Product::class],
            SpaRulesEnum::AccountSupplier => ['entity_1' => Account::class, 'entity_2' => Supplier::class],
            SpaRulesEnum::SupplierProduct => ['entity_1' => Supplier::class, 'entity_2' => Product::class],
            SpaRulesEnum::Account => ['entity_1' =>  Account::class, 'entity_2' => null],
            SpaRulesEnum::Supplier => ['entity_1' =>  Supplier::class, 'entity_2' => null],
            SpaRulesEnum::Product => ['entity_1' => Product::class, 'entity_2' => null],
            SpaRulesEnum::Global => ['entity_1' => Tenant::class, 'entity_2' => null],
        };
    }
}

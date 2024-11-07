<?php

namespace App\Helpers\Prices\Enums;

use App\Helpers\Prices\Rules\RuleUser;
use App\Helpers\Prices\Rules\RuleAccount;
use App\Helpers\Prices\Rules\RuleProduct;
use App\Helpers\Prices\Rules\RuleSupplier;
use App\Helpers\Prices\Rules\RuleUserAccount;
use App\Helpers\Prices\Rules\RuleUserSupplier;
use App\Helpers\Prices\Rules\RuleAccountProduct;
use App\Helpers\Prices\Rules\RuleAccountSupplier;
use App\Helpers\Prices\Rules\RuleSupplierProduct;


enum SpaRulesEnum: string
{
    case AccountProduct = RuleAccountProduct::class;
    case AccountSupplier = RuleAccountSupplier::class;
    case SupplierProduct = RuleSupplierProduct::class;
    case Account = RuleAccount::class;
    case Supplier = RuleSupplier::class;
    case Product = RuleProduct::class;
}

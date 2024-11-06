<?php

namespace App\Helpers\Prices;

use App\Models\Spa;
use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Prices\Rules\RuleUser;
use App\Helpers\Prices\PriceRulesObject;
use App\Helpers\Prices\Rules\RuleAccount;
use App\Helpers\Prices\Rules\RuleProduct;
use App\Helpers\Prices\Rules\RuleSupplier;
use App\Helpers\Prices\Rules\RuleUserAccount;
use App\Helpers\Prices\Rules\RuleUserSupplier;
use App\Helpers\Prices\Operations\OperationAdd;
use App\Helpers\Prices\Rules\RuleAccountProduct;
use App\Helpers\Prices\Rules\RuleAccountSupplier;
use App\Helpers\Prices\Rules\RuleSupplierProduct;
use App\Helpers\Prices\Operations\OperationDivide;
use App\Helpers\Prices\Operations\OperationMultiply;
use App\Helpers\Prices\Operations\OperationSubtract;

class PriceRules
{
    private $orderRules = [
        RuleAccountProduct::class,
        RuleAccountSupplier::class,
        RuleSupplierProduct::class,
        RuleUserAccount::class,
        RuleUserSupplier::class,
        RuleAccount::class,
        RuleSupplier::class,
        RuleProduct::class,
        RuleUser::class,
    ];

    private $operations = [
        OperationAdd::class,
        OperationSubtract::class,
        OperationMultiply::class,
        OperationDivide::class,
    ];

    public function __construct() {}

    public function calculate(float $price, Product $product = null, Account $account = null, Supplier $supplier = null): float
    {
        $object = new PriceRulesObject($product, Auth::user(), $supplier, $account);
        $object = app(Pipeline::class)->send($object)->through($this->orderRules)->thenReturn();
        if (is_null($object->spa) || !in_array($object->spa->operation, $this->operations)) {
            return $price;
        }

        $price = app($object->spa->operation)->apply($price, $object->spa);
        return $price;
    }
}

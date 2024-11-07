<?php

namespace App\Helpers\Prices;

use App\Helpers\Prices\Enums\SpaRulesEnum;
use App\Models\Spa;
use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Prices\PriceRulesObject;
use App\Helpers\Prices\Operations\OperationAdd;
use App\Helpers\Prices\Operations\OperationDivide;
use App\Helpers\Prices\Operations\OperationMultiply;
use App\Helpers\Prices\Operations\OperationSubtract;

class PriceRules
{
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
        $cases = array_map(fn($case) => $case->value, SpaRulesEnum::cases());

        $object = app(Pipeline::class)->send($object)->through($cases)->thenReturn();
        if (is_null($object->spa) || !in_array($object->spa->operation, $this->operations)) {
            return $price;
        }

        $price = app($object->spa->operation)->apply($price, $object->spa);
        return $price;
    }
}

<?php

namespace App\Helpers\Prices;

use App\Models\Spa;
use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Prices\PriceRulesObject;
use App\Helpers\Prices\Enums\SpaRulesEnum;

class PriceRules
{

    protected PriceRulesObject $object;

    public function calculate(float $price, int $product_id = null, int $supplier_id = null, int $account_id = null): float
    {
        $this->object = new PriceRulesObject($product_id, Auth::id(), $supplier_id, $account_id);
        $cases = array_map(fn($case) => $case->value, SpaRulesEnum::cases());

        $this->object = app(Pipeline::class)->send($this->object)->through($cases)->thenReturn();

        foreach ($this->object->spas as $spa) {
            $price = app($spa->operation->class)->apply($price, $spa);
        }

        return $price;
    }

    public function getAppliedSpas(): Collection
    {
        return $this->object->spas;
    }
}

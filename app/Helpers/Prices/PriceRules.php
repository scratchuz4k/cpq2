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

    protected static Collection $spas;
    public function __construct()
    {
        static::$spas = collect([]);
    }

    public function find(Product $product = null, Supplier $supplier = null, Account $account = null)
    {
        $this->object = new PriceRulesObject($product, Auth::user(), $supplier, $account);
        $cases = array_map(fn($case) => $case->value, SpaRulesEnum::cases());

        $this->object = app(Pipeline::class)->send($this->object)->through($cases)->thenReturn();
        static::$spas = $this->object->spas;
    }

    public function apply($price)
    {
        foreach (static::$spas as $spa) {
            $price = app($spa->operation->class)->apply($price, $spa);
        }

        return $price;
    }

    public function calculate(float $price, Product $product = null, Supplier $supplier = null, Account $account = null): float
    {
        $this->find($product,  $supplier,  $account);
        return $this->apply($price);
    }

    public function getAppliedSpas(): Collection
    {
        return static::$spas;
    }
}

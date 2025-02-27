<?php

namespace App\Helpers\Prices;

use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Prices\PriceRulesObject;
use App\Enums\SpaRulesEnum;
use App\Models\PricePlan;

class PriceRules
{
    protected PriceRulesObject $object;
    protected static Collection $spas;
    public function __construct()
    {
        static::$spas = collect([]);
    }

    public function find(Product $product = null, Supplier $supplier = null, Account $account = null, PricePlan $pricePlan = null)
    {
        $this->object = new PriceRulesObject($product, Auth::user(), $supplier, $account,);
        $cases = array_map(fn($case) => $case->value, SpaRulesEnum::cases());

        $this->object = app(Pipeline::class)->send($this->object)->through($cases)->thenReturn();
        if ($pricePlan) {
            static::$spas = $this->object->spas->filter(function ($spa) use ($pricePlan) {
                return is_null($spa->price_plan_id) || $spa->price_plan_id == $pricePlan->id;
            });
        } else {
            static::$spas = $this->object->spas->filter(function ($spa) {
                return is_null($spa->price_plan_id);
            });
        }
    }

    public function apply(float $price)
    {
        foreach (static::$spas as $spa) {
            $price = app($spa->operation->class)->apply($price, $spa);
        }

        return $price;
    }

    public function calculate(float $price, Product $product = null, Supplier $supplier = null, Account $account = null, PricePlan $pricePlan = null): float
    {
        $this->find($product,  $supplier,  $account, $pricePlan);
        return $this->apply($price);
    }

    public function getAppliedSpas(): Collection
    {
        return static::$spas;
    }

    public function setAppliedSpas(Collection $spas): void
    {
        static::$spas = collect($spas);
    }
}

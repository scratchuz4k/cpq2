<?php

namespace App\Helpers\Prices\Rules;

use App\Models\Spa;
use App\Models\SpaRule;
use App\Enums\SpaRulesEnum;

class RuleSupplierProduct
{
    public function handle($object, $next)
    {
        if (is_null($object->supplier) || is_null($object->product)) {
            return $next($object);
        }
        $rule = SpaRule::where("code", SpaRulesEnum::SupplierProduct->name)->first();
        $object->spas->push(
            ...Spa::where('field_1', $object->supplier->id)
                ->where('field_2', $object->product->id)
                ->where('rule_id',  $rule->id)
                ->get()
        );

        return $next($object);
    }
}

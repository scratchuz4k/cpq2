<?php

namespace App\Helpers\Prices\Rules;

use App\Models\Spa;
use App\Models\SpaRule;
use App\Helpers\Prices\PriceRulesObject;
use App\Helpers\Prices\Enums\SpaRulesEnum;

class RuleAccountProduct
{
    public function handle(PriceRulesObject $object, $next)
    {
        if (is_null($object->account_id) || is_null($object->product_id)) {
            return $next($object);
        }
        $rule = SpaRule::where("code", SpaRulesEnum::AccountProduct->name)->first();
        $object->spas->push(
            ...Spa::where('field_1', $object->account_id)
                ->where('field_2', $object->product_id)
                ->where('rule_id',  $rule->id)
                ->get()
        );

        return $next($object);
    }
}

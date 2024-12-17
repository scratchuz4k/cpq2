<?php

namespace App\Helpers\Prices\Rules;

use App\Models\Spa;
use App\Models\SpaRule;
use App\Helpers\Prices\PriceRulesObject;
use App\Enums\SpaRulesEnum;

class RuleAccountProduct
{
    public function handle(PriceRulesObject $object, $next)
    {
        if (is_null($object->account) || is_null($object->product)) {
            return $next($object);
        }
        $rule = SpaRule::where("code", SpaRulesEnum::AccountProduct->name)->first();
        $object->spas->push(
            ...Spa::where('field_1', $object->account->id)
                ->where('field_2', $object->product->id)
                ->where('rule_id',  $rule->id)
                ->get()
        );

        return $next($object);
    }
}

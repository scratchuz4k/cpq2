<?php

namespace App\Helpers\Prices\Rules;

use App\Models\Spa;
use App\Models\SpaRule;
use App\Helpers\Prices\PriceRulesObject;
use App\Enums\SpaRulesEnum;

class RuleAccountSupplier
{
    public function handle(PriceRulesObject $object, $next)
    {
        if (is_null($object->account) || is_null($object->supplier)) {
            return $next($object);
        }
        $rule = SpaRule::where("code", SpaRulesEnum::AccountSupplier->name)->first();
        $object->spas->push(
            ...Spa::where('field_1', $object->account->id)
                ->where('field_2', $object->supplier->id)
                ->where('rule_id',  $rule->id)
                ->get()
        );

        return $next($object);
    }
}

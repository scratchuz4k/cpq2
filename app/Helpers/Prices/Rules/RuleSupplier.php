<?php

namespace App\Helpers\Prices\Rules;

use App\Models\Spa;
use App\Models\SpaRule;
use App\Enums\SpaRulesEnum;

class RuleSupplier
{
    public function handle($object, $next)
    {
        if (is_null($object->supplier)) {
            return $next($object);
        }
        $rule = SpaRule::where("code", SpaRulesEnum::Supplier->name)->first();
        $object->spas->push(
            ...Spa::where('field_1', $object->supplier->id)
                ->where('field_2', null)
                ->where('rule_id',  $rule->id)
                ->get()
        );

        return $next($object);
    }
}

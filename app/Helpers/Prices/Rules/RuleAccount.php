<?php

namespace App\Helpers\Prices\Rules;

use App\Models\Spa;
use App\Models\SpaRule;
use App\Helpers\Prices\PriceRulesObject;
use App\Helpers\Prices\Enums\SpaRulesEnum;

class RuleAccount
{
    public function handle(PriceRulesObject $object, $next)
    {
        if (is_null($object->account_id)) {
            return $next($object);
        }
        $rule = SpaRule::where("code", SpaRulesEnum::Account->name)->first();
        $object->spas->push(
            ...Spa::where('field_1', $object->account_id)
                ->where('rule_id',  $rule->id)
                ->get()
        );

        return $next($object);
    }
}

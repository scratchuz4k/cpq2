<?php

namespace App\Helpers\Prices\Rules;

use App\Enums\SpaRulesEnum;
use App\Helpers\Prices\PriceRulesObject;
use App\Models\Spa;
use App\Models\SpaRule;

class RuleAccount
{
    public function handle(PriceRulesObject $object, $next)
    {
        if (is_null($object->account)) {
            return $next($object);
        }
        $rule = SpaRule::where('code', SpaRulesEnum::Account->name)->first();
        $object->spas->push(
            ...Spa::where('field_1', $object->account->id)
                ->where('field_2', operator: 0)
                ->where('rule_id', $rule->id)
                ->get()
        );

        return $next($object);
    }
}

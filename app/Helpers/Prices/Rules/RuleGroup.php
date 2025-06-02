<?php

namespace App\Helpers\Prices\Rules;

use App\Enums\SpaRulesEnum;
use App\Models\Spa;
use App\Models\SpaRule;

class RuleGroup
{
    public function handle($object, $next)
    {
        if (is_null($object->user?->group)) {
            return $next($object);
        }

        $rule = SpaRule::where('code', SpaRulesEnum::Group->name)->first();
        $object->spas->push(
            ...Spa::where('field_1', $object->user->group->id)
                ->where('field_2', 0)
                ->where('rule_id', $rule->id)
                ->get()
        );

        return $next($object);
    }
}

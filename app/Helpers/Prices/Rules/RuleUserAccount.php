<?php

namespace App\Helpers\Prices\Rules;

use App\Models\Spa;
use App\Models\User;

class RuleUserAccount
{
    public function handle($object, $next)
    {
        if (is_null($object->account)) {
            return $next($object);
        }

        $temp = User::ancestors();
        do {
            $current = $temp->pop();
            $object->spa =
                Spa::where('field_1', $current->id)
                ->where('field_2', $object->account->id)
                ->where('rule_type', RuleUserAccount::class)
                ->first();
            $temp->push(...$current->children);
        } while (!$object->spa && count($temp));

        if (is_null($object->spa)) {
            return $next($object);
        }
        return $object;
    }
}

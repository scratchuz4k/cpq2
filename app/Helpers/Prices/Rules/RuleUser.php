<?php

namespace App\Helpers\Prices\Rules;

use App\Models\Spa;
use App\Models\User;

class RuleUser
{
    public function handle($object, $next)
    {
        $temp = User::ancestors();
        do {
            $current = $temp->pop();
            $object->spa =
                Spa::where('field_1', $current->id)
                ->where('rule_type', RuleUser::class)
                ->first();
            $temp->push(...$current->children);
        } while (!$object->spa  && count($temp));

        if (is_null($object->spa)) {
            return $next($object);
        }

        return $object;
    }
}

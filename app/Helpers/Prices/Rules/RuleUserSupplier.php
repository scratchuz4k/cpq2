<?php

namespace App\Helpers\Prices\Rules;

use App\Models\Spa;
use App\Models\User;

class RuleUserSupplier
{
    public function handle($object, $next)
    {
        if (is_null($object->supplier)) {
            return $next($object);
        }

        $temp = User::ancestors();
        do {
            $current = $temp->pop();
            $object->spa =
                Spa::where('field_1', $current->id)
                ->where('field_2', $object->supplier->id)
                ->where('rule_type', RuleUserSupplier::class)
                ->first();
            $temp->push(...$current->children);
        } while (!$object->spa && count($temp));

        if (is_null($object->spa)) {
            return $next($object);
        }
        return $object;
    }
}

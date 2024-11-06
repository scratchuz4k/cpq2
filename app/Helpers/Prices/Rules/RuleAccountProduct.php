<?php

namespace App\Helpers\Prices\Rules;

class RuleAccountProduct
{
    public function handle($object, $next) {
        return $next($object);
    }
}

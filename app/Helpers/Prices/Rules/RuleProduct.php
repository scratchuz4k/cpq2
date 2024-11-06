<?php

namespace App\Helpers\Prices\Rules;

class RuleProduct
{
    public function handle($object, $next) {
        return $next($object);
    }
}

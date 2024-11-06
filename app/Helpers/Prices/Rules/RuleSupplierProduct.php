<?php

namespace App\Helpers\Prices\Rules;

class RuleSupplierProduct
{
    public function handle($object, $next) {
        return $next($object);
    }
}

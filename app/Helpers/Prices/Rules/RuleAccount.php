<?php

namespace App\Helpers\Prices\Rules;

class RuleAccount
{
    public function handle($object, $next) {
        return $next($object);
    }
}

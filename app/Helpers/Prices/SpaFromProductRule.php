<?php

namespace App\Helpers\Prices;

use App\Models\Price;

class SpaFromProductRule
{
    public function handle(Price $price, $next)
    {
        if (!$price->spa) {
            next($price);
        }


    }
}

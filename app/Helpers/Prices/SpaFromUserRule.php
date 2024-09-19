<?php

namespace App\Helpers\Prices;

use App\Models\Price;

class SpaFromUserRule
{
    public function handle(Price $price, $next)
    {
        if (!$price->spa) {
            next($price);
        }

        
    }
}

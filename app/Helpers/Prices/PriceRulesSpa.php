<?php

namespace App\Helpers\Prices;

use App\Models\Spa;


class PriceRulesSpa
{

    public function __construct() {}

    public function calculate(float $price, Spa $spa): float
    {
        if (!$price) {
            return 0;
        }

        return $price;
    }
}

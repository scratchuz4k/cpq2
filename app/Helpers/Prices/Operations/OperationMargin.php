<?php

namespace App\Helpers\Prices\Operations;

use App\Models\Spa;

class OperationMargin  implements _OperationInterface
{

    public function apply(float $value, Spa $spa): float
    {
        if ($spa->percentage > 0) {
            return $value + ($value * ($spa->percentage / 100));
        }

        return $value + $spa->value;
    }
}

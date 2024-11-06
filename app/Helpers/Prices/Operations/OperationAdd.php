<?php

namespace App\Helpers\Prices\Operations;

use App\Models\Spa;

class OperationAdd  implements _OperationInterface
{

    public function apply(float $value, ?Spa $spa): float
    {
        return $value + $spa->value;
    }
}

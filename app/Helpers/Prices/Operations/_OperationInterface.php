<?php

namespace App\Helpers\Prices\Operations;

use App\Models\Spa;

interface _OperationInterface
{
    public function apply(float $value, Spa $spa): float;
}

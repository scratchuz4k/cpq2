<?php

namespace App\Helpers\Prices\Enums;

use App\Helpers\Prices\Operations\OperationMargin;
use App\Helpers\Prices\Operations\OperationDiscount;

enum SpaOperationsEnum: string
{
    case Margin = OperationMargin::class;
    case Discount = OperationDiscount::class;
}

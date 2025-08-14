<?php

namespace App\Enums;

use App\Helpers\Prices\Operations\OperationDiscount;
use App\Helpers\Prices\Operations\OperationMargin;

enum SpaOperationsEnum: string
{
    case Margin = OperationMargin::class;
    case Discount = OperationDiscount::class;
}

<?php

namespace App\Enums;

use App\Helpers\Prices\Operations\OperationMargin;
use App\Helpers\Prices\Operations\OperationDiscount;

enum SpaOperationsEnum: string
{
    case Margin = OperationMargin::class;
    case Discount = OperationDiscount::class;
}

<?php

namespace App\Helpers\Prices\Enums;

use App\Helpers\Prices\Operations\OperationAdd;
use App\Helpers\Prices\Operations\OperationDivide;
use App\Helpers\Prices\Operations\OperationMultiply;
use App\Helpers\Prices\Operations\OperationSubtract;

enum SpaOperationsEnum: string
{
    case Add = OperationAdd::class;
    case Subtract = OperationSubtract::class;
    case Multiply = OperationMultiply::class;
    case Divide = OperationDivide::class;
}

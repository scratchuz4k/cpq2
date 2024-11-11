<?php

namespace App\Helpers\Prices\Enums;

enum PriceTypeEnum: string
{
    case Anual = 'anual';
    case Montlhy = 'monthly';
    case Single = 'single';
}

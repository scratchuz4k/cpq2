<?php

namespace App\Enums;

enum PricePlanEnum: string
{
    case MONTHLY = 'Recurring';
    case SINGLE = 'One-Off';
}

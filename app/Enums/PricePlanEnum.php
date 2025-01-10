<?php

namespace App\Enums;

enum PricePlanEnum: string
{
    case SINGLE = 'One-Off';
    case MONTHLY = 'Recurring';
}

<?php

namespace App\Enums;

enum PricePlanEnum: string
{
    case monthly = 'Recurring';
    case single = 'One-Off';
}

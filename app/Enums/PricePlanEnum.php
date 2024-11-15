<?php

namespace App\Enums;

enum PricePlanEnum: string
{
    case montlhy = 'Recurring';
    case single = 'One-Off';
}

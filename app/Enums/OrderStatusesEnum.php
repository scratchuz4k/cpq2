<?php

namespace App\Enums;

enum OrderStatusesEnum:string
{
    case REGISTRY = 'registry';
    case ORDERED = 'ordered';
    case REJECTED = 'rejected';
}

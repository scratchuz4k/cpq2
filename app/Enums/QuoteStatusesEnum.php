<?php

namespace App\Enums;

enum QuoteStatusesEnum:string
{
    case REGISTRY = 'registry';
    case QUOTE = 'quote';
    case PRICE = 'price';
    case CONFIGURE = 'configure';
}

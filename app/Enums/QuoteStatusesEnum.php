<?php

namespace App\Enums;

enum QuoteStatusesEnum: string
{
    case CONFIGURE = 'configure';
    case PRICE = 'price';
    case QUOTE = 'quote';
    case NEED_APPROVAL = 'need_approval';
    case VALIDATED = 'validated';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}

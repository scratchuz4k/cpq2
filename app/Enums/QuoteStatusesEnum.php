<?php

namespace App\Enums;

enum QuoteStatusesEnum: string
{
    case CONFIGURE = 'configure';
    case PRICE = 'price';
    case QUOTE = 'quote';
    case PENDING_APPROVAL = 'pending approval';
    case VALIDATED = 'validated';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
    case DECLINED = 'declined';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}

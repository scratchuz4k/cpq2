<?php

namespace App\Enums;

enum QuoteStatusesEnum: string
{
    case CONFIGURE = 'configure';
    case PRICE = 'price';
    case QUOTE = 'quote';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
    case REJECTED = 'rejected';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}

<?php

namespace App\Enums;

enum QuoteStatusesEnum: string
{
    case QUOTE = 'quote';
    case PRICE = 'price';
    case CONFIGURE = 'configure';
    case REGISTRY = 'registry';
    case ORDERED = 'ordered';
    case APPROVED = 'approved';
    case SUBMITTED = 'submitted';
    case REJECTED = 'rejected';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';
    case ON_HOLD = 'on hold';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}

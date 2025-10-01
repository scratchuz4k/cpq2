<?php

namespace App\Enums;

enum ImportStatusesEnum: string
{
    case COMPLETED = 'completed';
    case IN_PROGRESS = 'in progress';
    case CANCELLED = 'cancelled';
    case FAILURE = 'failure';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }
}

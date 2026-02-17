<?php

namespace App\Enums;

enum QuoteApprovalFlowEnum: string
{
    case INTERNAL = 'Internal review';
    case MARGIN = 'Margin review';
    case CLIENT = 'Client review';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], self::cases());
    }
}

<?php

namespace App\Enums;

enum QuoteApprovalFlowEnum: string
{
    case INTERNAL = 'Internal review';
    case MARGIN = 'Margin review';
    case CLIENT = 'Client review';

    public static function availableFlows(): array
    {
        return array_map(
            fn (self $case) => [
                'name' => $case->name,
                'value' => $case->value,
            ],
            array_filter(
                self::cases(),
                fn (self $case) => $case !== self::CLIENT,
            ),
        );
    }
}

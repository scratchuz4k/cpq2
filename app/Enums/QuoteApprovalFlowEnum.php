<?php

namespace App\Enums;

enum QuoteApprovalFlowEnum: string
{
    case Internal = 'internal';
    case Margin = 'margin';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], self::cases());
    }
}

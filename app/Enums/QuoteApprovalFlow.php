<?php

namespace App\Enums;

enum QuoteApprovalFlow: string
{
    case Internal = 'internal';
    case Margin = 'margin';
    case Account = 'account';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], self::cases());
    }
}

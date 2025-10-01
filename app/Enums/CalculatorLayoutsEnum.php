<?php

namespace App\Enums;

enum CalculatorLayoutsEnum: string
{
    case MARGIN = 'Show Margin only';
    case COMMISSION = 'Show Commission only';
    case BOTH = 'Show Margin and Commission';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], self::cases());
    }
}

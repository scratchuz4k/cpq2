<?php

namespace App\Enums;

enum CalculatorLayoutsEnum: string
{
    case MARGIN = 'Show Margin';
    case COMMISSION = 'Show Commission';
    case TOTAL = 'Show Total';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], self::cases());
    }
}

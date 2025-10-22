<?php

namespace App\Enums;

enum CalculatorLayoutsEnum: string
{
    case DEFAULT = 'Show Margin and Commission';
    case MARGIN = 'Show Margin only';
    case COMMISSION = 'Show Commission only';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], self::cases());
    }
}

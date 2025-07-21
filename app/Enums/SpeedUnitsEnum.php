<?php

namespace App\Enums;

enum SpeedUnitsEnum: string
{
    case MB = 'Mb';
    case GB = 'Gb';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], self::cases());
    }
}

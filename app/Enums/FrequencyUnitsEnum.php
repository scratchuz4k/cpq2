<?php

namespace App\Enums;

enum FrequencyUnitsEnum: string
{
    case DAYS = 'Days';
    case MONTHS = 'Months';
    case YEARS = 'Years';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
        ], self::cases());
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function daysPerUnit(): array
    {
        return [
            self::DAYS->name => 1,
            self::MONTHS->name => 30,
            self::YEARS->name => 360,
        ];
    }
}

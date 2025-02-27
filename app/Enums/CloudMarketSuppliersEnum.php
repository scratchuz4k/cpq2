<?php

namespace App\Enums;

enum CloudMarketSuppliersEnum: string
{
    case ITS = "its";
    case Virtual1 = "virtual1";
    case Gamma = "gamma";
    case Giacom = "giacom";
    case Vorboss = "vorboss";

    public static function asArray(): array
    {
        return array_map(fn($case) => [
            'name' => $case->name,   // Enum case value
            'value' => $case->value // Custom value
        ], self::cases());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}

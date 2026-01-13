<?php

namespace App\Enums;

enum CloudMarketSuppliersEnum: string
{
    case ITS = 'its';
    case Virtual1 = 'virtual1';
    case Gamma = 'gamma';
    case Giacom = 'giacom';
    case Vorboss = 'vorboss';
    case IPRiver = 'ipriver';
    case PXC = 'pxc';
    case VMO2 = 'vmo2';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,   // Enum case value
            'value' => $case->value, // Custom value
        ], self::cases());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

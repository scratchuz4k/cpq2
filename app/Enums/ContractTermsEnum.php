<?php

namespace App\Enums;

enum ContractTermsEnum: string
{
    case TWELVE = '12';
    case TWENTY_FOUR = '24';
    case THIRTY_SIX = '36';
    case SIXTY = '60';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,
            'value' => $case->value,
            'description' => $case->value.' months',
        ], self::cases());
    }
}

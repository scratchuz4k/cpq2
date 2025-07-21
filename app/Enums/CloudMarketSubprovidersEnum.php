<?php

namespace App\Enums;

enum CloudMarketSubprovidersEnum: string
{
    case BT = 'BT';
    case CITYFIBRE = 'CityFibre';
    case COLT = 'Colt';
    case EXPOE = 'ExpoE';
    case GAMMA_OPENREACH = 'Gamma Openreach';
    case GLIDE = 'Glide';
    case ITS = 'ITS';
    case M247_FIBRE = 'M247 Fibre';
    case NEOS_NETWORKS = 'Neos Networks';
    case PXC = 'PXC';
    case SKY = 'Sky';
    case SSE = 'SSE';
    case TALKTALK = 'TalkTalk';
    case TTB = 'TTB';
    case VIRGIN = 'Virgin';
    case VODAFONE = 'Vodafone';
    case VIRTUAL1 = 'Virtual1';

    public static function asArray(): array
    {
        return array_map(fn ($case) => [
            'name' => $case->name,   // Enum case value
            'value' => $case->value, // Custom value
        ], self::cases());
    }
}

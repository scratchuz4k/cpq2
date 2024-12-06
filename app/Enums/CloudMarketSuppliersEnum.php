<?php

namespace App\Enums;

enum CloudMarketSuppliersEnum: string
{
    case ITS = "its";
    case Virtual1 = "virtual1";
    case Gamma = "gamma";
    case Giacom = "giacom";

    public function getApiInfo(): array
    {
        return match ($this) {
            CloudMarketSuppliersEnum::ITS => ['auth_type' => 'access_key'],
            CloudMarketSuppliersEnum::Virtual1 => ['auth_type' => 'basic'],
            CloudMarketSuppliersEnum::Gamma => ['auth_type' => 'basic_client'],
            CloudMarketSuppliersEnum::Giacom => ['auth_type' => 'basic'],
        };
    }
}

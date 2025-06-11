<?php

namespace App\Enums;

enum SolutionTypesEnum: string
{
    case ConnectivityEthernet = '3';
    case Mobile = '4';
    case Hardware = '5';

    public function getName(): string
    {
        return match ($this) {
            SolutionTypesEnum::ConnectivityEthernet => 'Connectivity',
            SolutionTypesEnum::Mobile => 'Mobile',
            SolutionTypesEnum::Hardware => 'Hardware',
        };
    }
}

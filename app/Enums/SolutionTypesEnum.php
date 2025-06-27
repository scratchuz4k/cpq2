<?php

namespace App\Enums;

enum SolutionTypesEnum: string
{
    case ConnectivityEthernet = '3';
    case Mobile = '4';
    case Hardware = '5';
    case Cloud = '6';

    public function getName(): string
    {
        return match ($this) {
            SolutionTypesEnum::ConnectivityEthernet => 'Connectivity',
            SolutionTypesEnum::Mobile => 'Mobile',
            SolutionTypesEnum::Hardware => 'Hardware',
            SolutionTypesEnum::Cloud => 'Cloud',
        };
    }

    public static function getSteps(): array
    {
        return [
            'Connectivity' => ['StepAddress', 'StepOptionalFilters'],
            'Mobile' => [],
            'Hardware' => [],
            'Cloud' => ['StepCloudTenant', 'StepCloudTenantReview'],
        ];
    }
}

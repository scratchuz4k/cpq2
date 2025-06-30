<?php

namespace App\Enums;

enum SolutionTypesEnum: string
{
    case ConnectivityEthernet = '3';
    case Mobile = '4';
    case Hardware = '5';
    case CloudAudit = '6';

    public function getName(): string
    {
        return match ($this) {
            SolutionTypesEnum::ConnectivityEthernet => 'Connectivity',
            SolutionTypesEnum::Mobile => 'Mobile',
            SolutionTypesEnum::Hardware => 'Hardware',
            SolutionTypesEnum::CloudAudit => 'Cloud Audit',
        };
    }

    public static function getSteps(): array
    {
        return [
            'Connectivity' => ['StepAddress', 'StepOptionalFilters'],
            'Mobile' => [],
            'Hardware' => [],
            'Cloud Audit' => ['StepCloudAuditTenant', 'StepCloudAuditTenantReview'],
        ];
    }
}

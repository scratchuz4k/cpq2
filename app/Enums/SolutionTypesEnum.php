<?php

namespace App\Enums;

enum SolutionTypesEnum: string
{
    case ConnectivityEthernet = '3';
    case Mobile = '4';
    case Hardware = '5';
    case CloudAudit = '6';
    case Cloud = '7';
    case HostedVoice = '8';
    case Security = '9';
    case Microsoft = '10';
    case MicrosoftSecurity = '11';

    public function getName(): string
    {
        return match ($this) {
            SolutionTypesEnum::ConnectivityEthernet => 'Connectivity',
            SolutionTypesEnum::Mobile => 'Mobile',
            SolutionTypesEnum::Hardware => 'Hardware',
            SolutionTypesEnum::CloudAudit => 'Cloud Audit',
            SolutionTypesEnum::Cloud => 'Cloud',
            SolutionTypesEnum::HostedVoice => 'Hosted Voice',
            SolutionTypesEnum::Security => 'Security',
            SolutionTypesEnum::Microsoft => 'Microsoft',
            SolutionTypesEnum::MicrosoftSecurity => 'Microsoft vs Security',
        };
    }

    public static function getSteps(): array
    {
        return [
            'Connectivity' => ['StepAddress', 'StepOptionalFilters'],
            'Mobile' => [],
            'Hardware' => [],
            'Cloud Audit' => ['StepCloudAuditTenant'],
            'Cloud' => [],
            'Hosted Voice' => [],
            'Security' => [],
            'Microsoft' => [],
            'MicrosoftSecurity' => [],
        ];
    }
}

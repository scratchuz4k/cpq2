<?php

namespace App\Enums;

enum CloudAuditOperationEnum: string
{
    case ACCOUNT = 'ACCOUNT';
    case EMAIL = 'EMAIL';
    case REPORT = 'REPORT';
}

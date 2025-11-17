<?php

namespace App\Enums;

enum CloudAuditStatusEnum: string
{
    case CREATED_ACCOUNT = 'ACCOUNT CREATED ';
    case SENT_EMAIL = 'EMAIL SENT';
}

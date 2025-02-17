<?php

namespace App\Enums;

enum EmailTemplatesEnum: string
{
    case USER_PASSWORD_RECOVERY = 'User password recovery';
    case QUOTE_ACCOUNT_SHARE = 'Send quote to account user';
}

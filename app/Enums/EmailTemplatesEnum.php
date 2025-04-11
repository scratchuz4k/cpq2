<?php

namespace App\Enums;

enum EmailTemplatesEnum: string
{
    case USER_PASSWORD_RECOVERY = 'User password recovery';
    case SEND_QUOTE_TO_ACCOUNT = 'Send quote to associated account';
}

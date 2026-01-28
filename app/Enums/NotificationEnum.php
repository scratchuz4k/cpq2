<?php

namespace App\Enums;

enum NotificationEnum: string
{
    case QuoteSubmitted = 'quote_submitted';
    case QuoteApproved = 'quote_approved';
    case QuoteRejected = 'quote_rejected';
    case QuoteExpired = 'quote_expired';
}

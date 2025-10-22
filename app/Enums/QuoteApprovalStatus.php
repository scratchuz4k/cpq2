<?php

namespace App\Enums;

enum QuoteApprovalStatus: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
}

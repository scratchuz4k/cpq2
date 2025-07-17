<?php

namespace App\Services;

use App\Models\EmailTemplate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class EmailService
{
    public function update(EmailTemplate $emailTemplate, array $array): EmailTemplate
    {
        $tenant_id = Auth::user()->tenant_id;

        $emailTemplate->tenants()->sync([
            $tenant_id => Arr::only(
                [...$emailTemplate->toArray(), ...$array],
                ['to', 'cc', 'bcc', 'subject', 'body', 'is_active']
            ),
        ]);

        return $emailTemplate;
    }
}

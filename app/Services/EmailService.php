<?php

namespace App\Services;

use Illuminate\Support\Arr;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EmailService
{
    public function update(EmailTemplate $emailTemplate, array $array): EmailTemplate
    {
        $tenant_id = Auth::user()->tenant_id;

        $emailTemplate->tenants()->sync([
            $tenant_id => Arr::only($array, ['to', 'cc', 'bcc', 'subject', 'body'])
        ]);

        return $emailTemplate;
    }
}

<?php

namespace App\Observers;

use App\Models\Account;

class AccountObserver
{
    public function creating(Account $account)
    {
        $tenant_id = app('currentTenant')?->id;
        $account->tenant_id = $tenant_id ?: null;
    }
}

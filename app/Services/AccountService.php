<?php

namespace App\Services;

use App\Models\Account;
use App\Repositories\AccountRepository;
use Illuminate\Support\Facades\DB;

class AccountService
{
    public function __construct(protected AccountRepository $accountRepository) {}
    public function store(array $array): Account
    {
        return DB::transaction(function () use ($array) {
            $account = Account::create(
                [
                    ...$array,
                    'tenant_id' => app('currentTenant')->id,
                ]
            );

            if (isset($array['contacts']) && is_array($array['contacts'])) {
                $this->accountRepository->syncContacts($account, $array['contacts']);
            }

            return $account;
        });
    }

    public function update(Account $account, array $array): Account
    {
        return DB::transaction(function () use ($account, $array) {
            $account->update(
                [
                    ...$array,
                    'tenant_id' => app('currentTenant')->id,
                ]
            );

            if (isset($array['contacts']) && is_array($array['contacts'])) {
                $this->accountRepository->syncContacts($account, $array['contacts']);
            }

            return $account;
        });
    }
}

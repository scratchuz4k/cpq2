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
        $account = Account::create($array);

        if (isset($array['contacts']) && is_array($array['contacts'])) {
            $this->accountRepository->syncContacts($account, $array['contacts']);
        }

        return $account;
    }

    public function update(Account $account, array $array): Account
    {
        $account->update($array);

        if (isset($array['contacts']) && is_array($array['contacts'])) {
            $this->accountRepository->syncContacts($account, $array['contacts']);
        }

        return $account;
    }
}

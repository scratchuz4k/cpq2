<?php

namespace App\Services;

use App\Models\Account;
use App\Repositories\ContactRepository;
use Illuminate\Support\Facades\DB;

class AccountService
{

    public function __construct(protected ContactRepository $contactRepository) {}
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
                $this->contactRepository->sync($account, $array['contacts']);
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

            return $account;
        });
    }
}

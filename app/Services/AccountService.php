<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Repositories\AccountRepository;
use Illuminate\Support\Facades\Auth;

class AccountService
{
    public function __construct(protected AccountRepository $accountRepository) {}

    public function store(array $array): Account
    {
        $tenantId = Auth::user()->tenant_id;

        $account = Account::withTrashed()
            ->where('tenant_id', $tenantId)
            ->where('name', $array['name'])
            ->where('reference', $array['reference'])
            ->first();

        if ($account) {
            if ($account->trashed()) {
                $account->restore();
            }

            $account->update($array);
        } else {
            $account = Account::create($array);
        }

        if (isset($array['contacts']) && is_array($array['contacts'])) {
            $this->accountRepository->syncContacts($account, $array['contacts']);
        }

        return $account;
    }

    public function update(Account $account, array $array): Account
    {
        $account->update($array);

        $contactModel = new Contact;
        $formValues = array_diff($contactModel->getFillable(), ['tenant_id', 'account_id']);

        foreach ($array['contacts'] as $key => $contact) {
            $isEmpty = collect($contact)->only($formValues)->filter()->isEmpty();
            if ($isEmpty) {
                unset($array['contacts'][$key]);
            }
        }

        if (isset($array['contacts']) && is_array($array['contacts'])) {
            $this->accountRepository->syncContacts($account, $array['contacts']);
        }

        return $account;
    }
}

<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Repositories\AccountRepository;
use App\Support\GroupAssignment;
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

        $this->syncGroups($account, $array, allowFloor: true);

        return $account;
    }

    public function update(Account $account, array $array): Account
    {
        $account->update($array);

        $contactModel = new Contact;
        $formValues = array_diff($contactModel->getFillable(), ['tenant_id', 'account_id']);

        if (isset($array['contacts']) && is_array($array['contacts'])) {
            foreach ($array['contacts'] as $key => $contact) {
                $isEmpty = collect($contact)->only($formValues)->filter()->isEmpty();
                if ($isEmpty) {
                    unset($array['contacts'][$key]);
                }
            }

            $this->accountRepository->syncContacts($account, $array['contacts']);
        }

        $this->syncGroups($account, $array);

        return $account;
    }

    /**
     * On create ($allowFloor), a grouped actor that submits no groups gets the
     * account floored to their own groups. On update we only touch group
     * memberships when `groups` is explicitly supplied.
     */
    protected function syncGroups(Account $account, array $array, bool $allowFloor = false): void
    {
        $actor = Auth::user();
        $hasKey = array_key_exists('groups', $array) && is_array($array['groups']);

        if (! $hasKey && ! ($allowFloor && $actor && $actor->isGrouped())) {
            return;
        }

        $submitted = $hasKey ? $array['groups'] : null;

        $groupIds = GroupAssignment::resolve(
            $actor,
            $account->groups()->pluck('groups.id'),
            $submitted,
            'account',
        );

        $account->groups()->sync(
            collect($groupIds)->mapWithKeys(fn ($id) => [$id => ['tenant_id' => $actor->tenant_id]])->all()
        );
    }
}

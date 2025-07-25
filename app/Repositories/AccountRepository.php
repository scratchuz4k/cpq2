<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\Contact;

class AccountRepository
{
    public function syncContacts(Account $account, array $contacts)
    {
        $oldContacts = $account->contacts;

        foreach ($contacts as $item) {
            if (array_key_exists('id', $item)) {
                $contact = Contact::find($item['id']);

                $contact->update($item);

                $oldContacts = $oldContacts->reject(function ($contact) use ($item) {
                    return $contact->id === $item['id'];
                });
            } else {
                if (! empty($item)) {
                    $contact = Contact::create([...$item, 'account_id' => $account->id, 'tenant_id' => session()->get('tenant_id')]);
                }
            }
        }

        foreach ($oldContacts as $e) {
            $e->delete();
        }
    }
}

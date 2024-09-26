<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\Contact;

class ContactRepository
{
    public function sync(Account $account, array $contacts)
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
                if (array_key_exists('name', $item) && ! $item['name']) {
                    continue;
                }

                $contact = Contact::create([...$item, 'account_id' => $account->id]);
            }
        }

        foreach ($oldContacts as $e) {
            $e->delete();
        }
    }
}

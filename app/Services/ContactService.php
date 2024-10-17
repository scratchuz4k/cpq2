<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class ContactService
{

    public function store(array $array): Contact
    {
        return DB::transaction(function () use ($array) {
            $contact = Contact::create(
                [
                    ...$array,
                    'tenant_id' => session()->get('tenant_id'),
                ]
            );


            return $contact;
        });
    }

    public function update(Contact $contact, array $array): Contact
    {
        return DB::transaction(function () use ($contact, $array) {
            $contact->update(
                [
                    ...$array,
                    'tenant_id' => session()->get('tenant_id'),
                ]
            );

            return $contact;
        });
    }
}

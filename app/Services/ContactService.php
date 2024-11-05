<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;

class ContactService
{

    public function store(array $array): Contact
    {
        $contact = Contact::create($array,);
        return $contact;
    }

    public function update(Contact $contact, array $array): Contact
    {
        $contact->update($array);
        return $contact;
    }
}

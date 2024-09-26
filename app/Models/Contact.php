<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Observers\AccountObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['primary_contact_name', 'email', 'address_1', 'city', 'postcode', 'account_id'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}

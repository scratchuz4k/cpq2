<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = ['primary_contact_name', 'email', 'address_1', 'city', 'postcode', 'account_id', 'tenant_id'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

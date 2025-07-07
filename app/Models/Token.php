<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class Token extends SanctumPersonalAccessToken
{
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'tenant_id',
        'is_active',
    ];

    use HasFactory;

    protected $table = 'personal_access_tokens';

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
}

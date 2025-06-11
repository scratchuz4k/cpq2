<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Token extends SanctumPersonalAccessToken
{
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'tenant_id',
    ];

    use HasFactory;

    protected $table = 'personal_access_tokens';

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
}

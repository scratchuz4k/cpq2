<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'user_id',
        'tenant_id',
        'name',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
}

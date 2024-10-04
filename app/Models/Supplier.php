<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'reference',
        'tenant_id'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
}

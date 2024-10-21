<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SetTenantId;
    protected $fillable = [
        'name',
        'supplier_id',
        'brand_id',
        'tenant_id'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }
}

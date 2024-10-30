<?php

namespace App\Models;

use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function supplier(): HasOne
    {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }

    public function brand(): HasOne
    {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }
}

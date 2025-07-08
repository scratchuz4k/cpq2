<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = [
        'code',
        'name',
        'description',
        'supplier_id',
        'brand_id',
        "price_plan_id",
        'tenant_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean'
        ];
    }
    
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function activePrice(): ?Price
    {
        return $this->prices()->first();
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function pricePlan(): BelongsTo
    {
        return $this->belongsTo(PricePlan::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category');
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

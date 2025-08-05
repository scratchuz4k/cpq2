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
use Illuminate\Support\Carbon;

class Product extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'price_plan_id',
        'supplier_id',
        'brand_id',
        'code',
        'name',
        'status',
        'description',
        'is_active',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::saving(function (Product $product) {
            $product->status = Product::resolveStatus($product);
        });
    }

    public function activePrice(): ?Price
    {
        return $this->prices()->first();
    }

    public function parent()
    {
        return $this->hasOne(Product::class, 'id', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Product::class, 'parent_id', 'id');
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

    public static function resolveStatus(Product $product): string
    {

        $date = Carbon::parse($product->expires_at);
        if ($product->expires_at && $date->isPast()) {
            return 'Retired';
        }

        return $product->is_active ? 'Active' : 'Inactive';
    }
}

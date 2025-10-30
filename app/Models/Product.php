<?php

namespace App\Models;

use App\Enums\ProductStatusesEnum;
use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'category_id',
        'step_id',
        'sku',
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
            $status = Product::resolveStatus($product);

            if ($status === ProductStatusesEnum::RETIRED->value) {
                $product->is_active = false;
            }
            $product->status = $status;
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(Step::class);
    }

    public function supplier(): HasOne
    {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }

    public static function resolveStatus(Product $product): string
    {

        $date = Carbon::parse($product->expires_at);
        if ($product->expires_at && $date->isPast()) {
            return ProductStatusesEnum::RETIRED->value;
        }

        return $product->is_active ? ProductStatusesEnum::ACTIVE->value : ProductStatusesEnum::INACTIVE->value;
    }

    public static function descendants(int $product_id)
    {
        $product = Product::find($product_id);
        $descendants = collect();

        foreach ($product->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants($child->id));
        }

        return $descendants;
    }
}

<?php

namespace App\Models;

use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory, SetTenantId;
    protected $fillable = [
        "order_id",
        "product_code",
        "product_id",
        "quantity",
        "name",
        "data",
        'margin',
        'discount',
        'raw_price',
        'raw_price_monthly',
        'original_price',
        'original_price_monthly',
        'price',
        'price_monthly',
        'total',
        'total_tax',
        'total_gross',
        'total_monthly',
        'total_monthly_tax',
        'total_monthly_gross',
        'tax_order_item',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'object',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }

    public function order(): BelongsTo
    {
        related:
        return $this->belongsTo(Order::class);
    }
}

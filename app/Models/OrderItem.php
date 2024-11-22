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
        "price_plan_id",
        "product_code",
        "product_id",
        "quantity",
        "name",
        "data",
        'discount',
        'price',
        'total',
        'total_tax',
        'total_gross',
        'tax_order_item',
        "contract_length",
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

<?php

namespace App\Models;

use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

class OrderItem extends Model
{
    use HasFactory, SetTenantId;
    protected $fillable = [
        "order_id",
        "product_id",
        "quantity",
        "name",
        "data"
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

    public function order(): BelongsTo
    {
        related:
        return $this->belongsTo(Order::class);
    }
}

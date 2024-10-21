<?php

namespace App\Models;

use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use App\Observers\OrderItemObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([OrderItemObserver::class])]
class OrderItem extends Model
{
    use HasFactory, SetTenantId;
    protected $fillable = [
        "order_id",
        "product_id",
        "quantity",
        "name",
        "data",
        'price',
        'price_monthly',
        'price_install',
        'margin',
        'discount',
        'total',
        'total_monthly',
        'total_install',
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

    public function computeTotals()
    {
        $this->refresh();
        $this->total = 0;
        $this->total_monthly = 0;

        $this->total = $this->price * $this->quantity;
        $this->total_monthly = $this->price_monthly * $this->quantity;

        $this->total = ($this->total - ($this->total * ($this->discount / 100))) + (($this->total * ($this->margin / 100)));
        $this->total_monthly = ($this->total_monthly - ($this->total_monthly * ($this->discount / 100))) + (($this->total_monthly * ($this->margin / 100)));
        $this->save();
    }
}

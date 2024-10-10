<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Observers\OrderItemObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([OrderItemObserver::class])]
class OrderItem extends Model
{
    use HasFactory;
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

    public function save(array $options = []): bool
    {
        if (empty($this->tenant_id)) {
            $this->tenant_id = session()->get('tenant_id');
        }

        return parent::save($options);
    }

    public function order(): BelongsTo
    {related:
        return $this->belongsTo(Order::class);
    }
}

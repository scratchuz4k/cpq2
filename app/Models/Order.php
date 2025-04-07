<?php

namespace App\Models;

use App\Events\OrderStatusEvent;
use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = [
        'account_id',
        'solution_type_id',
        'address_line1',
        'address_postcode',
        'address_position',
        'address_city',
        'address_country',
        'address_data',
        'solution_type_id',
        'tenant_id',
        'name',
        'contract_length',
        'total',
        'total_tax',
        'total_gross',
        'total_monthly',
        'total_monthly_tax',
        'total_monthly_gross',
        'commission_value',
        'commission_rate',
        'margin',
        'discount',
        'status',
        'user_id',
        'quote_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'address_position' => 'object',
            'address_data' => 'object',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::saved(function ($order) {
            if ($order->isDirty('status')) {
                OrderStatusEvent::dispatch($order);
            }
        });

    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function solutionType(): BelongsTo
    {
        return $this->belongsTo(SolutionType::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}

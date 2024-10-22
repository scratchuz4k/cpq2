<?php

namespace App\Models;

use App\Observers\OrderObserver;
use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

class Order extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = [
        "account_id",
        "solution_type_id",
        "address_line1",
        "address_postcode",
        "address_position",
        "address_city",
        "address_country",
        "address_data",
        "solution_type_id",
        "tenant_id",
        "name",
        'total',
        'total_monthly',
        'total_install',
        'margin',
        'discount',
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
    public function computeTotals()
    {
        $this->refresh();
        $this->total = 0;
        $this->total_monthly = 0;

        foreach ($this->items as $item) {
            $this->total += $item->total;
            $this->total_monthly += $item->total_monthly;
        }

        $this->total = ($this->total - ($this->total * ($this->discount / 100))) + (($this->total * ($this->margin / 100)));
        $this->total_monthly = ($this->total_monthly - ($this->total_monthly * ($this->discount / 100))) + (($this->total_monthly * ($this->margin / 100)));
        $this->save();
    }

    // public function totals()
    // {
    //     $subtotal = 0;
    //     $subtotalOneoff = 0;

    //     foreach ($this->items as $item) {
    //         $subtotal += (float) $item->quantity * (float) ($item->data?->priceMonthly ?? 0);
    //         $subtotalOneoff += (float) $item->quantity * (float) ($item->data?->price ?? $item->data?->priceInstall ?? 0);
    //     }

    //     $totals['monthly_net'] = $subtotal;
    //     $totals['monthly_vat'] = $subtotal * 0.2;
    //     $totals['monthly_gross'] = $subtotal * 1.2;
    //     $totals['oneoff_net'] = $subtotalOneoff;
    //     $totals['oneoff_vat'] = $subtotalOneoff * 0.2;
    //     $totals['oneoff_gross'] = $subtotalOneoff * 1.2;

    //     return $totals;
    // }
}

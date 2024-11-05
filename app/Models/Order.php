<?php

namespace App\Models;

use App\Trait\SetTenantId;
use App\Observers\OrderObserver;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
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
        "contract_length",
        "total",
        "total_tax",
        "total_gross",
        "total_monthly",
        "total_monthly_tax",
        "total_monthly_gross",
        "comission_value",
        "commission_rate",
        "margin",
        "discount",
        "status",
        "user_id",
    ];
    protected function casts(): array
    {
        return [
            "address_position" => "object",
            "address_data" => "object",
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
        $original_total = 0;
        $original_total_monthly = 0;
        $this->total = 0;
        $this->total_tax = 0;
        $this->total_gross = 0;
        $this->total_monthly = 0;
        $this->total_monthly_tax = 0;
        $this->total_monthly_gross = 0;

        foreach ($this->items as $item) {
            $original_total += $item->original_price * $item->quantity;
            $original_total_monthly += $item->original_price_monthly * $item->quantity;

            $this->total += $item->total;
            $this->total_tax += $item->total_tax;
            $this->total_gross += $item->total_gross;

            $this->total_monthly += $item->total_monthly;
            $this->total_monthly_tax  += $item->total_monthly_tax;
            $this->total_monthly_gross += $item->total_monthly_gross;
        }

        $total_cost = $original_total + ($original_total_monthly * $this->contract_length);
        $margin_value = ($this->total + ($this->total_monthly * $this->contract_length)) - $total_cost;

        $this->margin = $total_cost ? ($margin_value / $total_cost) * 100 : 0;
        $this->comission_value = $margin_value * ($this->commission_rate / 100);
        $this->save();
    }
}

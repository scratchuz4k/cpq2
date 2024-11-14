<?php

namespace App\Models;

use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

class Quote extends Model
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
        "total",
        "total_tax",
        "total_gross",
        "commission_value",
        "commission_rate",
        "margin",
        "discount",
        "status",
        "user_id"
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
        return $this->hasMany(QuoteItem::class);
    }
    public function computeTotals()
    {
        $this->refresh();
        $raw_total = 0;

        $this->total = 0;
        $this->total_tax = 0;
        $this->total_gross = 0;

        foreach ($this->items as $item) {
            $raw_total += ($item->raw_price * $item->quantity) * $item->contract_length;

            $this->total += $item->total;
            $this->total_tax += $item->total_tax;
            $this->total_gross += $item->total_gross;
        }

        $total_cost = $raw_total;
        $margin_value = $this->total - $total_cost;

        $this->margin = $total_cost ? ($margin_value / $total_cost) * 100 : 0;
        $this->commission_value = $margin_value * ($this->commission_rate / 100);
        $this->save();
    }
}

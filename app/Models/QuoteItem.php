<?php

namespace App\Models;

use App\Enums\FrequencyUnitsEnum;
use App\Enums\QuoteApprovalFlowEnum;
use App\Models\Scopes\TenantScope;
use App\Trait\RegisterActivity;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteItem extends Model
{
    use HasFactory, RegisterActivity, SetTenantId, SoftDeletes;

    protected $fillable = [
        'quote_id',
        'price_plan_id',
        'product_id',
        'product_code',
        'quantity',
        'name',
        'data',
        'discount',
        'raw_price',
        'original_price',
        'price',
        'tax',
        'total',
        'total_tax',
        'total_gross',
        'renewal_terms_value',
        'renewal_terms_unit',
        'billing_frequency_value',
        'billing_frequency_unit',
        'tenant_id',
        'group',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'object',
            'raw_price' => 'float',
            'original_price' => 'float',
            'price' => 'float',
            'margin' => 'float',
            'discount' => 'float',
            'total' => 'float',
            'total_tax' => 'float',
            'total_gross' => 'float',
            'tax' => 'float',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::saved(function ($quoteItem) {
            $quoteItem->computeTotals();
            $quoteItem->saveQuietly();
            $quoteItem->quote->computeTotals();
            $quoteItem->quote->save();
        });

        static::deleted(function ($quoteItem) {
            $quoteItem->quote->computeTotals();
            $quoteItem->quote->save();
        });
    }

    /**
     * Get the parent user of this user.
     */
    public function parent()
    {
        return $this->hasOne(QuoteItem::class, 'id', 'parent_id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }

    public function quote(): BelongsTo
    {
        related:
        return $this->belongsTo(Quote::class);
    }

    public function pricePlan(): BelongsTo
    {
        related:
        return $this->belongsTo(PricePlan::class);
    }

    public function computeTotals()
    {
        $this->refresh();
        $price = $this->original_price;

        $price_line = $price * (1 - ($this->discount / 100));

        $this->price = $price_line;
        $this->margin = $this->price - $this->raw_price;
        $this->total = $this->price * $this->quantity * $this->numberOfPayments();
        $this->total_tax = $this->total * ($this->tax / 100);
        $this->total_gross = $this->total * (($this->tax / 100) + 1);
    }

    /**
     * Billing frequency unit, defaulting to months like numberOfPayments().
     */
    public function billingFrequencyUnit(): string
    {
        return $this->billing_frequency_unit ?: FrequencyUnitsEnum::MONTHS->name;
    }

    /**
     * Number of payments over the renewal term, i.e. how many times the
     * (discounted) price is charged: renewal term ÷ billing frequency.
     */
    public function numberOfPayments(): float
    {
        $daysPerUnit = FrequencyUnitsEnum::daysPerUnit();
        $monthDays = $daysPerUnit[FrequencyUnitsEnum::MONTHS->name];

        $renewalDays = ($this->renewal_terms_value ?: 1)
            * ($daysPerUnit[$this->renewal_terms_unit] ?? $monthDays);

        $billingDays = ($this->billing_frequency_value ?: 1)
            * ($daysPerUnit[$this->billing_frequency_unit] ?? $monthDays);

        return $billingDays > 0 ? $renewalDays / $billingDays : 1.0;
    }

    public function isWithinMarginThresholds(): bool
    {
        $hasPemission = get_tenant_config('has_quote_approval');
        $isActive = get_tenant_config('quote_approval_flow') === QuoteApprovalFlowEnum::MARGIN->value;

        if (! is_null($hasPemission) && $hasPemission == '1' && $isActive) {
            $margin = $this->price ? ($this->margin / $this->price) * 100 : 0;
            $operation = get_tenant_config('quote_margin_rules_operation');
            $value = (float) get_tenant_config('quote_margin_rules_value');

            return $operation == 'percentage' ? $margin >= $value : $this->margin * $this->quantity >= $value;
        }

        return true;
    }
}

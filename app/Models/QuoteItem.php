<?php

namespace App\Models;

use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuoteItem extends Model
{
    use HasFactory, SetTenantId;
    protected $fillable = [
        "quote_id",
        "product_id",
        "price_plan_id",
        "parent_id",
        "product_code",
        "quantity",
        "name",
        "data",
        'margin',
        'discount',
        'raw_price',
        'original_price',
        'price',
        'total',
        'total_tax',
        'total_gross',
        'tax_quote_item',
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

        static::updating(function ($quoteItem) {
            $quoteItem->computeTotals();
        });

        static::saved(function ($quoteItem) {
            $quoteItem->quote->computeTotals();
        });

        static::deleted(function ($quoteItem) {
            $quoteItem->quote->computeTotals();
        });
    }
    /**
     * Get the parent user of this user.
     */
    public function parent()
    {
        return $this->hasOne(QuoteItem::class, 'id', 'parent_id');
    }

    /**
     * Get the children users of this user.
     */
    public function children()
    {
        return $this->hasMany(QuoteItem::class, 'parent_id', 'id');
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
        $price = $this->original_price;

        $discounts = $this->quote->discounts->where('discountable_type', PricePlan::class)->where('discountable_id', $this->pricePlan->id)->pluck('discount');
        $discounts  = [$this->discount, ...$discounts];

        foreach ($discounts as $discount) {
            $price = ($price * (1 - ($discount / 100)));
        }

        $this->price = $price;
        $this->margin = $this->price - $this->raw_price;
        $this->total = ($this->price * $this->quantity);
        $this->total_tax = ($this->total * ($this->tax_quote_item / 100));
        $this->total_gross = ($this->total * (($this->tax_quote_item / 100) + 1));
    }
}

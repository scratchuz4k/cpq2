<?php

namespace App\Models;

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
        'contract_length',
        'tenant_id',
        'group',
    ];

    protected function casts(): array
    {
        return [
            'raw_price' => 'float',
            'original_price' => 'float',
            'price' => 'float',
            'margin' => 'float',
            'discount' => 'float',
            'total' => 'float',
            'total_tax' => 'float',
            'total_gross' => 'float',
            'tax' => 'float',
            'created_at' => 'datetime:y-m-d H:i:s',
            'updated_at' => 'datetime:y-m-d H:i:s',
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

        $discounts = $this->quote->discounts->where('discountable_type', PricePlan::class)->where('discountable_id', $this->pricePlan->id)->pluck('discount');
        $discounts = [$this->discount, ...$discounts];

        foreach ($discounts as $discount) {
            $price = format_currency(($price * (1 - ($discount / 100))));
        }

        $this->price = $price;
        $this->margin = $this->price - $this->raw_price;
        $this->total = format_currency($this->price * $this->quantity * $this->contract_length);
        $this->total_tax = format_currency($this->total * ($this->tax / 100));
        $this->total_gross = format_currency($this->total * (($this->tax / 100) + 1));
    }
}

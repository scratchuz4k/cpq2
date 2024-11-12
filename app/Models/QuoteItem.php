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
        "product_code",
        "product_id",
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
    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }

    public function quote(): BelongsTo
    {
        related:
        return $this->belongsTo(Quote::class);
    }

    public function computeTotals()
    {
        $this->refresh();
        $price = $this->original_price + (($this->original_price * ($this->margin / 100)));

        $discounts  = [$this->discount, $this->quote->discount];
        foreach ($discounts as $discount) {
            $price = ($price * (1 - ($discount / 100)));
        }

        $this->price = $price;

        $this->total = $this->price * $this->quantity;
        $this->total_tax = $this->total * ($this->tax_quote_item / 100);
        $this->total_gross = $this->total * (($this->tax_quote_item / 100) + 1);
        $this->save();
    }
}

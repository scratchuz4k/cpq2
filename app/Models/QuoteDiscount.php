<?php

namespace App\Models;

use App\Models\Quote;
use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteDiscount extends Model
{
    use SetTenantId;

    protected $fillable = ["discountable_type", "discountable_id", "discount", "tenant_id"];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::saved(function ($discount) {
            foreach ($discount->quote->itemsNoTree as $item) {
                $item->computeTotals();
                $item->saveQuietly();
            };
            $discount->quote->computeTotals();
            $discount->quote->save();
        });

        static::deleted(function ($discount) {
            foreach ($discount->quote->itemsNoTree as $item) {
                $item->computeTotals();
                $item->saveQuietly();
            };
            $discount->quote->computeTotals();
            $discount->quote->save();
        });
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}

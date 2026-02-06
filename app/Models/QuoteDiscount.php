<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\RegisterActivity;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteDiscount extends Model
{
    use RegisterActivity, SetTenantId, SoftDeletes;

    protected $fillable = ['quote_id', 'discountable_type', 'discountable_id', 'discount', 'tenant_id', 'total'];

    protected function casts(): array
    {
        return [
            'discount' => 'float',
            'value' => 'float',
            'total' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::saved(function ($discount) {
            $discount->value = 0;
            $discount->total = 0;
            $discount->saveQuietly();

            foreach ($discount->quote->items as $item) {
                $item->computeTotals();
                $item->saveQuietly();
            }
            $discount->quote->computeTotals();
            $discount->quote->save();
        });

        static::deleted(function ($discount) {
            foreach ($discount->quote->items as $item) {
                $item->computeTotals();
                $item->saveQuietly();
            }
            $discount->quote->computeTotals();
            $discount->quote->save();
        });
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}

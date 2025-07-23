<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\RegisterActivity;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteDiscount extends Model
{
    use RegisterActivity, SetTenantId;

    protected $fillable = ['discountable_type', 'discountable_id', 'discount', 'tenant_id'];

    protected function casts(): array
    {
        return [
            'discount' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::saved(function ($discount) {
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

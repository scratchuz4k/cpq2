<?php

namespace App\Models;

use App\Helpers\Prices\PriceRules;
use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * All financial data will be stored as integers to eradicate rounding/floating point issues
 * - The most decimal places used by any world currency is 3
 * - The most decimal places used by bulk product unit prices is 4
 * - The most decimal places used by crazy financial brokers and stock traders is 6
 *
 * To remain flexible all integers will represent one millionth (10^-6)
 * - £0.000001 = 1
 * - £0.01 = 10000
 * - $0.000325 = 3250
 * - £9,450.00 = 9450000000
 * - 20% = 20000000
 *
 * MySQL BIGINT(8) SIGNED can store up to 9223372036854775807
 * - With six DP of precision this means our largest currency value is:
 * - 9,223,372,036,854.775807 (9 trillion)
 */
class Price extends Model
{
    use HasFactory, SetTenantId;

    protected static PriceRules $priceRules;

    protected $fillable = [
        'code',
        'cost',
        'net',
        'product_id',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::retrieved(function ($model) {
            // TODO: switch to a account saved in the user model
            static::$priceRules = new PriceRules;
            $account = Account::find(request() ? request()->input('account_id', null) : null);
            static::$priceRules->find($model->product, $model->product->supplier, $account, $model->product->pricePlan);
            $model->setAttribute('net_spa', static::$priceRules->apply((float) $model->net));
            $model->setAttribute('active_spas', static::$priceRules->getAppliedSpas());
        });

        static::saving(function ($model) {
            unset($model->attributes['net_spa']);
            unset($model->attributes['active_spas']);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function pricePlan(): BelongsTo
    {
        related:
        return $this->belongsTo(PricePlan::class);
    }
}

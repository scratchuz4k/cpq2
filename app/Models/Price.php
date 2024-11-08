<?php

namespace App\Models;

use App\Helpers\Prices\PriceRules;
use App\Helpers\Prices\PriceRulesSpa;
use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 *
 */
class Price extends Model
{
    use HasFactory, SetTenantId;

    protected static PriceRules $priceRules;

    public function __construct()
    {
        parent::__construct();
        static::$priceRules = new PriceRules();
    }
    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($model) {
            static::$priceRules->find($model->product, $model->product->supplier,);
            $model->setAttribute('net_spa', static::$priceRules->apply((float)$model->net));
            $model->setAttribute('net_monthly_spa', static::$priceRules->apply((float)$model->net_monthly));
            $model->setAttribute('active_spas', static::$priceRules->getAppliedSpas());
            $model->calculate();
        });
    }

    protected $fillable = [
        "code",
        "tax_rate",
        "net",
        "tax",
        "gross",
        "net_monthly",
        "tax_monthly",
        "gross_monthly",
        "spa_id",
        "product_id",
        "tenant_id"
    ];


    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, "product_id", "id");
    }

    public function calculate()
    {
        $this->tax = $this->net * ($this->tax_rate / 100);
        $this->tax_monthly = $this->net_spa * ($this->tax_rate / 100);

        $this->gross = $this->net_spa * (1 + ($this->tax_rate / 100));
        $this->gross_monthly = $this->net_monthly_spa * (1 + ($this->tax_rate / 100));
    }
}

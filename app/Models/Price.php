<?php

namespace App\Models;

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

    public function __construct(protected PriceRulesSpa $priceRulesSpa)
    {
        parent::__construct();
    }

    protected $fillable = ["code", "tax_rate", "net", "tax", "gross", "net_monthly", "tax_monthly", "gross_monthly", "spa_id", "product_id", "tenant_id"];

    protected $appends = ["net_spa", "net_monthly_spa"];


    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function spa(): HasOne
    {
        return $this->hasOne(Spa::class, "id", "spa_id");
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, "id", "product_id");
    }

    public function calculate()
    {
        $this->refresh();
        $this->tax = $this->net * ($this->tax_rate / 100);
        $this->tax_monthly = $this->net_monthly * ($this->tax_rate / 100);

        $this->gross = $this->net_monthly * (1 + ($this->tax_rate / 100));
        $this->gross_monthly = $this->net_monthly * (1 + ($this->tax_rate / 100));
        $this->save();
    }
    public function getNetSpaAttribute()
    {
        return $this->priceRulesSpa->calculate($this->net, $this->spa);
    }
    public function getNetMonthlySpaAttribute()
    {
        return $this->priceRulesSpa->calculate($this->net_monthly, $this->spa);
    }
}

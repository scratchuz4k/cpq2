<?php

namespace App\Models;

use Money\Currency;
use Money\Currencies;
use Money\MoneyParser;
use Money\MoneyFormatter;
use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Log;
use Money\Currencies\ISOCurrencies;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Parser\IntlLocalizedDecimalParser;
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

    protected $fillable = ["tax_rate", "net", "tax", "gross", "spa_id", "product_id", "tenant_id"];

    protected static $supplier_cache = [];
    protected static $ancestor_cache = [];
    protected static $spa_cache = [];

    protected $table = 'prices';

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
        return $this->hasOne(Spa::class, 'id', 'spa_id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
    public static function calculate(array $params): array
    {
        $supplier = null;
        $user_id = Auth::id();

        if (!isset(static::$ancestor_cache[$user_id])) {
            $userAncestors = User::ancestors(Auth::id());
            $userAncestors[] = $user_id;
            static::$ancestor_cache[$user_id] = $userAncestors;
        } else {
            $userAncestors = static::$ancestor_cache[$user_id];
        }

        if (!empty($params['supplier'])) {
            if (!isset(static::$supplier_cache[$params['supplier']])) {
                if ($supplier = Supplier::where('reference', $params['supplier'])->first()) {
                    $supplier_id = $supplier->id;
                    static::$supplier_cache[$params['supplier']] = $supplier_id ?: false;
                }
            } else {
                $supplier_id = static::$supplier_cache[$params['supplier']];
            }

            if (!empty($supplier_id)) {
                if (!isset(static::$spa_cache[$supplier_id])) {
                    Log::debug('calc spa check for supplier:' . $supplier_id . ', users: ' . json_encode($userAncestors));
                    // Check for SPA associated with this specific user, if not found, check for ancestors
                    $spa = Spa::where('supplier_id', $supplier_id)
                        ->where('user_id', $user_id)
                        ->first() ?: Spa::where('supplier_id', $supplier_id)
                        ->whereIn('user_id', $userAncestors)
                        ->first();
                    static::$spa_cache[$supplier_id] = $spa ?: false;
                } else {
                    $spa = static::$spa_cache[$supplier_id] ?? false;
                }

                if ($spa && !empty($spa->data)) {
                    $output = $params['data'] ?? [];

                    $currency = new Currency('GBP');
                    $currencies = new ISOCurrencies();
                    $numberFormatter = new \NumberFormatter('en_GB', \NumberFormatter::DECIMAL);
                    $moneyParser = new IntlLocalizedDecimalParser($numberFormatter, $currencies);
                    $outputFormatter = new DecimalMoneyFormatter($currencies);

                    foreach ($params['prices'] as $price_field) {
                        if (isset($output[$price_field])) {
                            $output[$price_field] = static::reprice($output[$price_field], $spa, $currency, $currencies, $numberFormatter, $moneyParser, $outputFormatter);
                        }
                    }

                    return $output;
                }
            }
        }

        return $params['data'] ?? [];
    }

    public static function reprice($price, Spa $spa, Currency $currency, Currencies $currencies, \NumberFormatter $formatter, MoneyParser $parser, MoneyFormatter $ouputFormatter)
    {
        $money = $parser->parse($price, $currency);

        $spa_data = json_decode($spa->data, 1);

        foreach ($spa_data as $operation => $value) {
            // supports add, subtract, multiply, divide
            if ($operation == 'ADD' || $operation == 'SUBTRACT') {
                $valueMoney = $parser->parse($value, $currency);
                $money = $money->$operation($valueMoney);
            } else {
                $money = $money->$operation($value);
            }
        }

        $outputFormatter = new DecimalMoneyFormatter($currencies);
        return $outputFormatter->format($money);
    }
}

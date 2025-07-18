<?php

namespace App\Helpers\Prices;

use Money\Currency;
use App\Models\User;
use Money\Currencies\ISOCurrencies;
use Illuminate\Support\Facades\Auth;
use Money\Formatter\DecimalMoneyFormatter;
use App\Models\Supplier;
use Money\Parser\IntlLocalizedDecimalParser;

class PriceRulesConnectivity
{

    private  $currency;
    private  $currencies;
    private  $numberFormatter;
    private  $moneyParser;
    private  $outputFormatter;
    protected static $spa = [];

    public function __construct()
    {
        $this->currency = new Currency('GBP');
        $this->currencies = new ISOCurrencies();
        $this->numberFormatter = new \NumberFormatter('en_GB', \NumberFormatter::DECIMAL);
        $this->moneyParser = new IntlLocalizedDecimalParser($this->numberFormatter, $this->currencies);
        $this->outputFormatter = new DecimalMoneyFormatter($this->currencies);
    }

    public function calculate(array $product)
    {
        if (!array_key_exists('providerKey', $product) && $product['providerKey']) {
            return $product;
        }

        $supplier = Supplier::where('reference', $product['providerKey'])->first();
        if (!$supplier) {
            return $product;
        }

        if (!array_key_exists($supplier->id, static::$spa)) {
            // For each User and User Ancestors find the 1st with valid SPA by supplier
            static::$spa[$supplier->id] = null;
            $temp = User::ancestors(Auth::id());
            do {
                $current = $temp->pop();
                static::$spa[$supplier->id] = $current->spas->where('supplier_id', $supplier->id)->first() ?? null;
                $temp->push(...$current->children);
            } while (!static::$spa[$supplier->id]  && count($temp));
        }

        return $this->calculateSpa($product, static::$spa[$supplier->id]);
    }

    private function calculateSpa(array $product, $spa)
    {
        if (is_null($spa) || !$spa->data) {
            return $product;
        }
        $operation = strtolower((string) $spa->data->operation);
        $spaValue = $this->moneyParser->parse($spa->data->value, fallbackCurrency: $this->currency);
        if (!in_array($operation, ['add', 'subtract', 'multiply', 'divide'])) {
            return $product;
        }

        foreach (['priceMonthly', 'priceAnnual', 'priceInstall'] as $key) {
            if (!array_key_exists($key, $product)) {
                continue;
            }

            if (in_array($operation, ['add', 'subtract'])) {
                $priceMoney = $this->moneyParser->parse($product[$key], $this->currency);
                $product[$key] = $this->outputFormatter->format($spaValue->$operation($priceMoney));
            } else {
                $product[$key] = $this->outputFormatter->format($spaValue->$operation($product[$key]));
            }
        }

        return $product;
    }
}

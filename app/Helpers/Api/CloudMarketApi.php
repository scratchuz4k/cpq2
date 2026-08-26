<?php

namespace App\Helpers\Api;

use App\Enums\CloudMarketSuppliersEnum;
use App\Models\Api;
use App\Models\Quote;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudMarketApi
{
    private static int $requestTimeout = 600;

    public static function getQuotes(Quote $quote, CloudMarketAddress $address)
    {
        $quoteAddress = self::buildQuoteAddress($quote, $address);
        $credentials = self::collectCredentials();

        $params = array_merge($quoteAddress, $credentials);
        Log::debug('CloudMarketApi::getQuotes lookup:', $quoteAddress);

        return self::makeRequest('/PartnerQuoteService/GetQuotes', $params);
    }

    public static function getFailover(Quote $quote)
    {
        $product = self::getPrimaryProduct($quote);
        $provider = self::resolveProvider($product);

        $apis = self::fetchApisForProvider($provider);
        $credentials = self::collectCredentials($apis);

        $params = [
            'ProductId' => $product->data?->productCode,
            'QuoteId' => $product->data?->quoteId,
        ];

        if ($product->data?->isManaged !== null) {
            $params['IsManaged'] = $product->data->isManaged ? 'true' : 'false';
        }

        if ($product->data?->connectivityType !== null) {
            $params['ConnectivityType'] = $product->data->connectivityType;
        }

        Log::debug('CloudMarketApi::getFailover lookup:', $params);

        return self::makeRequest('/PartnerQuoteService/GetFailover', array_merge($params, $credentials));
    }

    public static function getAddons(Quote $quote)
    {
        $product = self::getPrimaryProduct($quote);
        $failover = self::getFailoverProduct($quote);
        $provider = self::resolveProvider($product);

        $apis = self::fetchApisForProvider($provider);
        $credentials = self::collectCredentials($apis);

        $params = [
            'ProductId' => $product->data?->productCode,
            'QuoteId' => $product->data?->quoteId,
            'FailoverProductId' => $failover?->data?->productCode ?? null,
        ];

        if ($product->data?->isManaged !== null) {
            $params['IsManaged'] = $product->data->isManaged ? 'true' : 'false';
        }

        if ($product->data?->connectivityType !== null) {
            $params['ConnectivityType'] = $product->data->connectivityType;
        }

        Log::debug('CloudMarketApi::getAddons lookup:', $params);

        return self::makeRequest('/PartnerQuoteService/GetHardware', array_merge($params, $credentials));
    }

    public static function getOptions(Quote $quote)
    {
        $product = self::getPrimaryProduct($quote);
        $failover = self::getFailoverProduct($quote);
        $primaryHardware = self::getHardwareProduct($quote, 'Primary Hardware Product');
        $failoverHardware = self::getHardwareProduct($quote, 'Failover Hardware Product');
        $provider = self::resolveProvider($product);

        $apis = self::fetchApisForProvider($provider);
        $credentials = self::collectCredentials($apis);

        $params = [
            'ProductId' => $product->data?->productCode,
            'QuoteId' => $product->data?->quoteId,
            'FailoverProductId' => $failover?->data?->productCode ?? null,
            'PrimaryHardwareProductId' => $primaryHardware?->data?->productCode ?? null,
            'FailoverHardwareProductId' => $failoverHardware?->data?->productCode ?? null,
        ];

        if ($product->data?->isManaged !== null) {
            $params['IsManaged'] = $product->data->isManaged ? 'true' : 'false';
        }

        if ($product->data?->connectivityType !== null) {
            $params['ConnectivityType'] = $product->data->connectivityType;
        }

        Log::debug('CloudMarketApi::getOptions lookup:', $params);

        return self::makeRequest('/PartnerQuoteService/GetOptions', array_merge($params, $credentials));
    }

    public static function getAssignments(Quote $quote)
    {
        $product = self::getPrimaryProduct($quote);
        $failover = self::getFailoverProduct($quote);
        $primaryHardware = self::getHardwareProduct($quote, 'Primary Hardware Product');
        $failoverHardware = self::getHardwareProduct($quote, 'Failover Hardware Product');
        $provider = self::resolveProvider($product);

        $apis = self::fetchApisForProvider($provider);
        $credentials = self::collectCredentials($apis);

        $params = [
            'ProductId' => $product->data?->productCode,
            'QuoteId' => $product->data?->quoteId,
            'FailoverProductId' => $failover?->data?->productCode ?? null,
            'PrimaryHardwareProductId' => $primaryHardware?->data?->productCode ?? null,
            'FailoverHardwareProductId' => $failoverHardware?->data?->productCode ?? null,
        ];

        if ($product->data?->isManaged !== null) {
            $params['IsManaged'] = $product->data->isManaged ? 'true' : 'false';
        }

        if ($product->data?->connectivityType !== null) {
            $params['ConnectivityType'] = $product->data->connectivityType;
        }

        Log::debug('CloudMarketApi::getAssignments lookup:', $params);

        return self::makeRequest('/PartnerQuoteService/GetIPAssignments', array_merge($params, $credentials));
    }

    public static function saveQuote(Quote $quote)
    {
        $product = self::getPrimaryProduct($quote);
        $provider = self::resolveProvider($product);

        $apis = self::fetchApisForProvider($provider);
        $credentials = self::collectCredentials($apis);

        $params = [
            'productId' => $product->data?->productCode,
            'quoteId' => $product->data?->quoteId,
            'addOns' => self::saveAddons($quote),
            'customerName' => str_pad($quote->account->name, 5, '_'),
            'customReference' => str_pad($quote->account->reference, 5, '_'),
            'isManaged' => $product->data?->isManaged ?? null,
        ];

        if (is_array($quote->data) && isset($quote->data['quoteReference'])) {
            $params['quoteReference'] = $quote->data['quoteReference'];
        }

        Log::debug('CloudMarketApi::saveQuote request:', $params);

        $credentials = Arr::undot($credentials);

        return self::makeRequestPost('/PartnerQuoteService/SaveQuote', array_merge($params, $credentials));
    }

    public static function getStatistics(string $createdSince)
    {
        $params = [
            'CreatedSince' => $createdSince,
        ];

        return self::makeRequest('/PartnerQuoteService/GetResponseStats', $params);
    }

    private static function collectCredentials($apis = null): array
    {
        if (! $apis) {
            $apis = Api::active()->whereIn('code', CloudMarketSuppliersEnum::values())->get();
        }

        $credentials = [];
        foreach ($apis as $api) {
            $credentials += self::resolveApiAuth($api);
        }

        return array_filter($credentials, fn ($value) => ! empty($value));
    }

    private static function getPrimaryProduct(Quote $quote)
    {
        $products = collect($quote['items'] ?? $quote->items)->where(fn ($x) => in_array(data_get($x, 'data.type'), ['Primary Product'], true))->values();
        if (! count($products)) {
            throw new Exception('No Primary Product Available');
        }

        return $products[0];
    }

    private static function getFailoverProduct(Quote $quote)
    {
        $failovers = collect($quote['items'] ?? $quote->items)->where(fn ($x) => in_array(data_get($x, 'data.type'), ['Failover Product'], true))->values();

        return count($failovers) ? $failovers[0] : null;
    }

    private static function getHardwareProduct(Quote $quote, string $type)
    {
        $hardware = collect($quote['items'] ?? $quote->items)->where(fn ($x) => in_array(data_get($x, 'data.type'), [$type], true))->values();

        return count($hardware) ? $hardware[0] : null;
    }

    private static function saveAddons(Quote $quote)
    {
        $temp = [];
        $addons = collect($quote['items'] ?? $quote->items)->where(fn ($x) => ! in_array(data_get($x, 'data.type'), ['Primary Product'], true))->values();

        $temp = $addons->map(function ($addon) {
            return [
                'id' => isset($addon->data?->quoteId) ? data_get($addon->data, 'quoteId') : data_get($addon->data, 'id'),
                'options' => [
                    [
                        'id' => data_get($addon->data, 'productCode'),
                        'quantity' => data_get($addon, 'quantity', 0),
                    ],
                ],
            ];
        })->all();

        return $temp;
    }

    private static function resolveProvider($product): string
    {
        return $product->data?->providerKey == 'pxc' ? 'virtual1' : $product->data?->providerKey;
    }

    private static function fetchApisForProvider(string $provider)
    {
        return Api::active()->whereIn('code', CloudMarketSuppliersEnum::values())->where('code', $provider)->get();
    }

    private static function buildQuoteAddress(Quote $quote, CloudMarketAddress $address): array
    {
        $quoteAddress = [
            'ConnectivityType' => 3,
            'Address1.Postcode' => $address->postCode,
            'Address1.Latitude' => $address->lat,
            'Address1.Longitude' => $address->lon,
            'Address1.UPRN' => $address->uprn,
            'Address1.DistrictCode' => $address->districtCode,
            'Address1.AddressKey' => $address->addressKey,
            'Address1.BuildingName' => $address->buildingName,
            'Address1.BuildingNumber' => $address->buildingNumber,
            'Address1.SubPremises' => $address->subPremises,
            'Address1.Street' => $address->street,
            'Address1.Town' => $address->town,
            'ManagedOption' => 0,
            'TermMonths' => ! empty($quote->configs['contract_length']) ? explode(',', (string) $quote->configs['contract_length']) : [],
        ];

        $connectionSpeeds = [];
        if (isset($quote->configs['bearer']) && is_numeric($quote->configs['bearer'])) {
            $connectionSpeeds['ConnectionSpeeds.MinBearer'] = (int) $quote->configs['bearer'];
            $connectionSpeeds['ConnectionSpeeds.MaxBearer'] = 100000;
        }

        if (isset($quote->configs['speed']) && is_numeric($quote->configs['speed'])) {
            $connectionSpeeds['ConnectionSpeeds.MinSpeed'] = (int) $quote->configs['speed'];
            $connectionSpeeds['ConnectionSpeeds.MaxSpeed'] = 100000;
        }

        if (! empty($connectionSpeeds)) {
            $quoteAddress = array_merge($quoteAddress, $connectionSpeeds);
        }

        if (! in_array(config('app.env'), ['local', 'testing'], true)) {
            $quoteAddress = array_merge(['RequestId' => $quote->uuid], $quoteAddress);
        }

        return $quoteAddress;
    }

    private static function makeRequest(string $endpoint, array $params)
    {
        $baseUrl = config('services.cloudmarket.connection_service_url');

        return Http::withOptions([
            'stream' => true,
            'timeout' => self::$requestTimeout,
            'connect_timeout' => 30,
        ])
            ->timeout(self::$requestTimeout)
            ->withToken(config('services.alpnet.api_token'))
            ->get($baseUrl.$endpoint, $params);
    }

    private static function makeRequestPost(string $endpoint, array $params)
    {
        $baseUrl = config('services.cloudmarket.connection_service_url');

        return Http::withOptions([
            'timeout' => self::$requestTimeout,
            'connect_timeout' => 30,
        ])
            ->timeout(self::$requestTimeout)
            ->withToken(config('services.alpnet.api_token'))
            ->post($baseUrl.$endpoint, $params);
    }

    private static function resolveApiAuth($api): array
    {
        $supplierKey = CloudMarketSuppliersEnum::tryFrom($api?->code)?->name;
        if (! $supplierKey) {
            return [];
        }

        $connectionBase = "SupplierApiConnections.{$supplierKey}_Connection";

        return match ($api?->auth_type) {
            'basic' => [
                "{$connectionBase}.Username" => $api?->username ?? config('services.'.strtolower($supplierKey).'.connection_username'),
                "{$connectionBase}.Password" => $api?->password ?? config('services.'.strtolower($supplierKey).'.connection_password'),
            ],
            'basic_client' => [
                "{$connectionBase}.Username" => $api?->username ?? config('services.'.strtolower($supplierKey).'.connection_username'),
                "{$connectionBase}.Password" => $api?->password ?? config('services.'.strtolower($supplierKey).'.connection_password'),
                "{$connectionBase}.ClientId" => $api?->client_id ?? config('services.'.strtolower($supplierKey).'.connection_client_id'),
            ],
            'token' => [
                "{$connectionBase}.Token" => $api?->token ?? config('services.'.strtolower($supplierKey).'.connection_token'),
            ],
            'vmo2_client' => [
                "{$connectionBase}.AppID" => $api?->app_id ?? config('services.'.strtolower($supplierKey).'.connection_app_id'),
                "{$connectionBase}.ClientSecret" => $api?->token ?? config('services.'.strtolower($supplierKey).'.connection_client_secret'),
                "{$connectionBase}.UserId" => $api?->username ?? config('services.'.strtolower($supplierKey).'.connection_user_id'),
                "{$connectionBase}.CustomerId" => $api?->client_id ?? config('services.'.strtolower($supplierKey).'.connection_customer_id'),
            ],

            default => [],
        };
    }
}

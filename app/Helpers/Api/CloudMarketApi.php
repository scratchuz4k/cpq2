<?php

namespace App\Helpers\Api;

use App\Enums\CloudMarketSuppliersEnum;
use App\Models\Api;
use App\Models\Quote;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudMarketApi
{
    private static int $requestTimeout = 420;

    public static function getQuotes(Quote $quote, CloudMarketAddress $address)
    {
        $quoteAddress = self::buildQuoteAddress($quote, $address);
        $credentials = self::collectCredentials();

        $params = array_merge($quoteAddress, $credentials);
        Log::debug('CloudMarketApi::getQuotes lookup:', $quoteAddress);

        return self::makeRequest('/PartnerQuoteService/GetQuotes', $params);
    }

    public static function getFailover(Quote $quote, CloudMarketAddress $address)
    {
        $product = self::getPrimaryProduct($quote);
        $provider = self::resolveProvider($product);

        $apis = self::fetchApisForProvider($provider);
        $credentials = self::collectCredentials($apis);

        $params = [
            'ProductId' => $product->data?->productCode,
            'QuoteId' => $product->data?->quoteId,
        ];
        Log::debug('CloudMarketApi::getFailover lookup:', $params);

        return self::makeRequest('/PartnerQuoteService/GetFailover', array_merge($params, $credentials));
    }

    public static function getAddons(Quote $quote, CloudMarketAddress $address)
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
        Log::debug('CloudMarketApi::getAddons lookup:', $params);

        return self::makeRequest('/PartnerQuoteService/GetHardware', array_merge($params, $credentials));
    }

    public static function getOptions(Quote $quote, CloudMarketAddress $address)
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
            'PrimaryHardwareProductId' => $primaryHardware?->data?->productReference ?? null,
            'FailoverHardwareProductId' => $failoverHardware?->data?->productReference ?? null,
        ];
        Log::debug('CloudMarketApi::getOptions lookup:', $params);

        return self::makeRequest('/PartnerQuoteService/GetOptions', array_merge($params, $credentials));
    }

    public static function getAssignments(Quote $quote, CloudMarketAddress $address)
    {
        $product = self::getPrimaryProduct($quote);
        $failover = self::getFailoverProduct($quote);
        $primaryHardware = self::getHardwareProduct($quote, 'Primary Hardware Product');
        $failoverHardware = self::getHardwareProduct($quote, 'Failover Hardware Product');
        $provider = self::resolveProvider($product);

        $apis = self::fetchApisForProvider($provider);
        $credentials = self::collectCredentials();

        $params = [
            'ProductId' => $product->data?->productCode,
            'QuoteId' => $product->data?->quoteId,
            'FailoverProductId' => $failover?->data?->productCode ?? null,
            'PrimaryHardwareProductId' => $primaryHardware?->data?->productReference ?? null,
            'FailoverHardwareProductId' => $failoverHardware?->data?->productReference ?? null,
        ];
        Log::debug('CloudMarketApi::getAssignments lookup:', $params);

        return self::makeRequest('/PartnerQuoteService/GetIPAssignmentsOptions', array_merge($params, $credentials));
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
            'Address1.AddressKey' => $address->addressKey,
            'Address1.BuildingName' => $address->buildingName,
            'Address1.BuildingNumber' => $address->buildingNumber,
            'Address1.SubPremises' => $address->subPremises,
            'Address1.Street' => $address->street,
            'Address1.Town' => $address->town,
            'ManagedOption' => $quote->configs['managed'] ?? 0,
            'TermMonths' => $quote->configs['contract_length'] ? explode(',', (string) $quote->configs['contract_length']) : [],
            'ConnectionSpeeds.MinBearer' => is_numeric($quote->configs['bearer'] ?? null) ? (int) $quote->configs['bearer'] : 100,
            'ConnectionSpeeds.MaxBearer' => 100000,
            'ConnectionSpeeds.MinSpeed' => is_numeric($quote->configs['speed'] ?? null) ? (int) $quote->configs['speed'] : 10,
            'ConnectionSpeeds.MaxSpeed' => 100000,
        ];

        return $quoteAddress;
    }

    private static function makeRequest(string $endpoint, array $params)
    {
        $baseUrl = env('CLOUDMARKET_CONNECTION_SERVICE_URL', 'https://cpq-poc-uat-api.cloudmarket.com');

        return Http::withOptions([
            'stream' => true,
            'timeout' => self::$requestTimeout,
            'connect_timeout' => 30,
        ])
            ->timeout(self::$requestTimeout)
            ->withToken(env('ALPNET_API_TOKEN'))
            ->get($baseUrl.$endpoint, $params);
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
                "{$connectionBase}.Username" => $api?->username ?? env(strtoupper("{$supplierKey}_CONNECTION_USERNAME")),
                "{$connectionBase}.Password" => $api?->password ?? env(strtoupper("{$supplierKey}_CONNECTION_PASSWORD")),
            ],
            'basic_client' => [
                "{$connectionBase}.Username" => $api?->username ?? env(strtoupper("{$supplierKey}_CONNECTION_USERNAME")),
                "{$connectionBase}.Password" => $api?->password ?? env(strtoupper("{$supplierKey}_CONNECTION_PASSWORD")),
                "{$connectionBase}.ClientId" => $api?->client_id ?? env(strtoupper("{$supplierKey}_CONNECTION_CLIENTID")),
            ],
            'token' => [
                "{$connectionBase}.Token" => $api?->token ?? env(strtoupper("{$supplierKey}_CONNECTION_TOKEN")),
            ],
            default => [],
        };
    }
}

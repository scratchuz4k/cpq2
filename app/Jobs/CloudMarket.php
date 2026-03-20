<?php

namespace App\Jobs;

use App\Enums\CloudMarketSubprovidersEnum;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Jobs\TenantAware;

class CloudMarket implements ShouldQueue, TenantAware
{
    use Queueable;

    public $timeout = 3600;

    public $prices = [];

    public $errors = [];

    public $started_at = null;

    public $tempFilters = [];

    public $subproviders = [];

    public function __construct()
    {
        $subproviders = CloudMarketSubprovidersEnum::asArray();
        // Remove spaces and lowercase the value string for each subprovider
        foreach ($subproviders as $subprovider) {
            $this->subproviders[strtolower(str_replace(' ', '', $subprovider['value']))] = $subprovider['value'];
        }

    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        $className = static::class;
        $message = '['.strtoupper((string) config('app.env')).'] '.$className.' failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'CPQ',
            'response' => $exception->getMessage(),
        ];
        $errors = [$error];

        $file = $this->getProductFile();
        if ($file) {
            $errors = array_merge($errors, $file?->errors ?? []);
            $message = '['.strtoupper((string) config('app.env')).'] '.$className.' failed with error: '.json_encode($errors);
            $this->writeProductOnFailure($file, $errors);
        }

        Log::error($message);
        if (config('services.discord.cloudmarket.jobs')) {
            Http::post(config('services.discord.cloudmarket.jobs'), ['content' => $message]);
        }
    }

    protected function getConsolidatedProviderName($provider): string
    {
        $key = strtolower(str_replace(' ', '', $provider));
        if (array_key_exists($key, $this->subproviders)) {
            $provider = $this->subproviders[$key];
        }

        $provider_override = [
            'virtual1' => 'PXC',
        ];

        return array_key_exists(strtolower($provider), $provider_override) ? $provider_override[strtolower($provider)] : $provider;
    }

    protected function getConsolidatedProviderGroup($provider): string
    {
        $sub_provider_mapping = [
            'BT' => ['BT Wholesale', 'BTW', 'BT', 'BTO'],
            'Virgin' => ['Virgin Media', 'Virgin Media Business', 'Virgin'],
            'ITS' => ['ITS Technology Group', 'ITS'],
            'TalkTalk' => ['TalkTalk Business', 'TalkTalk', 'TTB'],
        ];

        foreach ($sub_provider_mapping as $consolidated_name => $sub_providers) {
            if (in_array($provider, $sub_providers)) {
                return $consolidated_name;
            }
        }

        return $provider;
    }

    protected function processErrors($supplier, $message)
    {
        $unauthorizedMessages = [
            'Request failed with status code Unauthorized',
            'Un-authorised user',
            'Bad credentials',
        ];

        foreach ($unauthorizedMessages as $unauthorizedMessage) {
            if (str_contains((string) $message, $unauthorizedMessage)) {
                $message = 'Request failed with status Unauthorized';
                break;
            }
        }

        return [
            'provider' => $supplier,
            'response' => $message,
        ];
    }

    protected function getProductFile()
    {
        return null;
    }

    protected function writeProductOnFailure($file, array $errors): void
    {
        // Override in subclasses if needed
    }
}

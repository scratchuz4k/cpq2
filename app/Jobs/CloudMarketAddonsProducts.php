<?php

namespace App\Jobs;

use App\Helpers\Api\CloudMarketAddress;
use App\Helpers\Api\CloudMarketApi;
use App\Models\Cloudmarket\Product;
use App\Models\Quote;
use Exception;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JsonMachine\Items;
use Spatie\Multitenancy\Jobs\TenantAware;

class CloudMarketAddonsProducts implements ShouldQueue, TenantAware
{
    use Queueable;

    public $timeout = 3600;

    public $prices = [];

    public $errors = [];

    public $started_at = null;

    public $tempFilters = [
        'providerName' => [],
        'connectivitySupplierGroup' => [],
        'contractTermMonths' => [],
        'bearer' => [],
        'speed' => [],
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(protected Quote $quote, protected string $file, protected CloudMarketAddress $address) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $res = CloudMarketApi::getAddons($this->quote, $this->address);
        $time = Carbon::now()->addSeconds(86400);

        $this->started_at = now();
        $product = collect($this->quote->items)->where(function ($x) {
            return in_array(data_get($x, 'data.type'), ['Primary Product'], true);
        })->values();

        $stream = StreamWrapper::getResource($res->toPsrResponse()->getBody());
        foreach (Items::fromStream($stream) as $value) {
            if (! is_object($value) && ! is_array($value)) {
                continue;
            }
            foreach ($value as $item) {
                $temp = (array) $item;
                $temp['code'] = '';
                foreach (array_keys($temp) as $itemKey) {
                    if (! (is_string($temp[$itemKey]) || is_numeric($temp[$itemKey]))) {
                        continue;
                    }
                    $temp['code'] .= $temp[$itemKey];
                }

                $temp['providerKey'] = $product[0]->data?->providerKey == 'pxc' ? 'virtual1' : $product[0]->data?->providerKey;
                $this->prices[] = [...$temp, 'code' => md5(Str::slug($temp['code']))];
                Product::write('addons', $this->file, $this->prices, false, null, $this->started_at, $this->tempFilters, $this->errors, $time);
            }
        }

        Product::write('addons', $this->file, $this->prices, false, now(), $this->started_at, $this->tempFilters, $this->errors, $time);
        Log::info($res->toPsrResponse()->getBody()->getContents());
    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) env('APP_ENV')).'] CloudMarketConnectivityProducts failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'CPQ',
            'response' => $exception->getMessage(),
        ];
        $errors = [];

        $file = Product::where('key', 'fail_over')->where('filename', $this->file)->first();
        $errors[] = $error;
        array_merge($errors, $file?->errors ?? []);
        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) env('APP_ENV')).'] CloudMarketConnectivityProducts failed with error: '.json_encode($errors);

        Product::write('fail_over', $this->file, $file['data'], false, now(), $file['created_at'], $file['filters'], $errors);

        Log::error($message);
        if (env('DISCORD_CLOUDMARKET_JOBS')) {
            Http::post(env('DISCORD_CLOUDMARKET_JOBS'), ['content' => $message]);
        }
    }

    private function getConsolidatedProviderGroup($provider): string
    {
        $sub_provider_mapping = [
            'BT' => ['BT Wholesale', 'BTW', 'BT'],
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

    private function processErrors($supplier, $message)
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
}

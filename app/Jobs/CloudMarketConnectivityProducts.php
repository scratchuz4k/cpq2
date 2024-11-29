<?php

namespace App\Jobs;

use Exception;
use JsonMachine\Items;
use Illuminate\Support\Str;
use App\Helpers\FilterProvider;
use GuzzleHttp\Psr7\StreamWrapper;
use App\Helpers\Api\CloudMarketApi;
use Illuminate\Support\Facades\Log;
use App\Helpers\Api\CloudMarketFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Helpers\Api\CloudMarketAddress;
use Spatie\Multitenancy\Jobs\TenantAware;
use Illuminate\Foundation\Queue\Queueable;
use App\Repositories\CloudMarketRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class CloudMarketConnectivityProducts implements ShouldQueue, TenantAware
{
    use Queueable;
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(protected CloudMarketFile $file, protected $connectivityType, protected CloudMarketAddress $address) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $res = CloudMarketApi::getQuotes($this->connectivityType, $this->address);
        $started_at = now();
        if (!$this->file->exists()) {
            throw new Exception("CloudMarketConnectivityProducts: File was not Found!");
        }

        $prices = [];
        $errors = [];
        $tempFilters = [
            "providerName" => [],
            "connectivitySupplierName" => [],
            "contractTermMonths" => [],
            'bearer' => [],
            'speed' => []
        ];

        $stream = StreamWrapper::getResource($res->toPsrResponse()->getBody());
        foreach (Items::fromStream($stream) as $key => $value) {
            if ($value->hasErrors && $value->quoteResponseError) {
                $error = [
                    'provider' => $value->quoteResponseError->requestInfo->supplier,
                    'response' => $value->quoteResponseError->errorMessage,
                ];

                if (!in_array($error, $errors)) {
                    $errors[] = $error;
                }
            }

            if (empty($value->providerKey) || !$value->quoteId) {
                continue;
            }

            $temp = (array)$value;
            $temp['code'] = '';
            foreach (array_keys($temp) as $itemKey) {
                if (!(is_string($temp[$itemKey]) || is_numeric($temp[$itemKey]))) {
                    continue;
                }
                $temp['code'] .= $temp[$itemKey];
            }

            $prices[] = [...$temp, 'code' => md5(Str::slug($temp['code']))];
            $tempFilters = FilterProvider::process($tempFilters, $temp);
            $this->file->write($prices, false, null, $started_at, $tempFilters, $errors);
        }

        $this->file->write($prices, false, now(), $started_at, $tempFilters, $errors);
    }

    /**
     * The job failed to process.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        $message = "CloudMarketConnectivityProducts failed with error: " . $exception->getMessage();
        Log::error($message);
        Http::post(env("DISCORD_CLOUDMARKET_JOBS"), ["content" =>  $message]);
    }
}

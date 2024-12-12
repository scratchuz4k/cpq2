<?php

namespace App\Jobs;

use App\Repositories\CloudMarketRepository;
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
use Illuminate\Contracts\Queue\ShouldQueue;

class CloudMarketConnectivityProducts implements ShouldQueue, TenantAware
{
    use Queueable;
    public $timeout = 3600;
    public $prices = [];
    public $errors = [];
    public $started_at = null;
    public $tempFilters = [
        "providerName" => [],
        "connectivitySupplierGroup" => [],
        "contractTermMonths" => [],
        "bearer" => [],
        "speed" => []
    ];

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
        $this->started_at = now();
        if (!$this->file->exists()) {
            throw new Exception("CloudMarketConnectivityProducts: File was not Found!");
        }

        $stream = StreamWrapper::getResource($res->toPsrResponse()->getBody());
        foreach (Items::fromStream($stream) as $key => $value) {
            if ($value->hasErrors && $value->quoteResponseError) {
                $error = $this->processErrors($value->quoteResponseError->requestInfo->supplier, $value->quoteResponseError->errorMessage);

                if (!in_array($error, $this->errors)) {
                    $this->errors[] = $error;
                }
            }

            if (empty($value->providerKey) || !$value->quoteId) {
                continue;
            }

            $temp = (array)$value;
            $temp["providerKey"] = $temp["providerKey"]  == 'virtual1' ? 'pxc' : $temp["providerKey"];
            $temp["providerName"] = $temp["providerName"]  == 'Virtual1' ? 'PXC' : $temp["providerName"];
            $temp["connectivitySupplierName"] = $temp["connectivitySupplierName"]  == 'Virtual1' ? 'PXC' : $temp["connectivitySupplierName"];
            $temp["connectivitySupplierGroup"] = $this->getConsolidatedProviderGroup($temp['connectivitySupplierName']);
            $temp["code"] = "";
            foreach (array_keys($temp) as $itemKey) {
                if (!(is_string($temp[$itemKey]) || is_numeric($temp[$itemKey]))) {
                    continue;
                }
                $temp["code"] .= $temp[$itemKey];
            }

            $this->prices[] = [...$temp, "code" => md5(Str::slug($temp["code"]))];
            $this->tempFilters = FilterProvider::process($this->tempFilters, $temp);
            $this->file->write($this->prices, false, null, $this->started_at, $this->tempFilters, $this->errors);
        }

        $this->file->write($this->prices, false, now(), $this->started_at, $this->tempFilters, $this->errors);
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
        $message = "[" . strtoupper(env("APP_ENV")) . "] CloudMarketConnectivityProducts failed with error: " . $exception->getMessage();

        $error = [
            "provider" => "JOB",
            "response" =>  $exception->getMessage(),
        ];

        $file = $this->file->getContent();
        $file['errors'][] = $error;

        // Handle the failure, log exception details, etc.
        $message = "[" . strtoupper(env("APP_ENV")) . "] CloudMarketConnectivityProducts failed with error: " . json_encode($file['errors']);

        $this->file->write($file['data'], false, now(), $file['created_at'], $file['filters'], $file['errors']);
        Log::error($message);
        if (env("DISCORD_CLOUDMARKET_JOBS")) {
            Http::post(env("DISCORD_CLOUDMARKET_JOBS"), ["content" =>  $message]);
        }
    }

    private function getConsolidatedProviderGroup($provider): string
    {
        $sub_provider_mapping = [
            'BT' => ['BT Wholesale', 'BTW', 'BT'],
            'Virgin' => ['Virgin Media', 'Virgin Media Business', 'Virgin'],
            'ITS' => ['ITS Technology Group', 'ITS'],
            'TalkTalk' => ['TalkTalk Business', 'TalkTalk']
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
        if (str_contains($message, "Request failed with status code Unauthorized")) {
            $message = "Request failed with status Unauthorized";
        }

        return [
            "provider" => $supplier,
            "response" => $message,
        ];
    }
}

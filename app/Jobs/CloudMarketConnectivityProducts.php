<?php

namespace App\Jobs;

use Exception;
use JsonMachine\Items;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\StreamWrapper;
use App\Helpers\Api\CloudMarketApi;
use Illuminate\Support\Facades\Log;
use App\Helpers\Api\CloudMarketFile;
use Illuminate\Support\Facades\Cache;
use App\Helpers\Api\CloudMarketAddress;
use App\Repositories\CloudMarketRepository;
use Spatie\Multitenancy\Jobs\TenantAware;
use Illuminate\Foundation\Queue\Queueable;
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
        $cloudMarketRepository = new CloudMarketRepository();
        $res = CloudMarketApi::getQuotes($this->connectivityType, $this->address);
        $started_at = now();
        if (!$this->file->exists()) {
            throw new Exception("CloudMarketConnectivityProducts: File was not Found!");
        }

        $prices = [];
        $filters = [
            "providerName" => [],
            "connectivitySupplierName" => [],
            "contractTermMonths" => [],
            'bearer' => [],
            'speed' => []
        ];

        $stream = StreamWrapper::getResource($res->toPsrResponse()->getBody());
        foreach (Items::fromStream($stream) as $key => $value) {
            if (empty($value->providerKey)|| !$value->quoteId) {
                continue;
            }
            $temp = (array)$value;
            $temp['code'] = '';
            foreach (array_keys($temp) as $key) {
                if (!(is_string($temp[$key]) || is_numeric($temp[$key]))) {
                    continue;
                }
                $temp['code'] .= $temp[$key];
            }
            $prices[] = [...$temp, 'code' => md5(Str::slug($temp['code']))];

            $filters = $cloudMarketRepository->processFilters($filters, $temp);
            $this->file->write($prices, false, null, $started_at, $filters);
        }

        $this->file->write($prices, false, now(), $started_at, $filters);
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
        Log::error("CloudMarketConnectivityProducts failed with message: " . $exception->getMessage());
    }
}

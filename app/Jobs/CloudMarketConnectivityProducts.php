<?php

namespace App\Jobs;

use App\Helpers\Api\CloudMarketAddress;
use App\Helpers\Api\CloudMarketApi;
use App\Helpers\FilterProvider;
use App\Models\Cloudmarket\Product;
use App\Models\Quote;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use JsonMachine\Items;

class CloudMarketConnectivityProducts extends CloudMarket
{
    public $tempFilters = [
        'providerName' => [],
        'connectivitySupplierGroup' => [],
        'contractTermMonths' => [],
        'bearer' => [],
        'speed' => [],
        'isManaged' => [],
    ];

    public function __construct(protected Quote $quote, protected string $file, protected CloudMarketAddress $address)
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $res = CloudMarketApi::getQuotes($this->quote, $this->address);
        $time = Carbon::now()->addSeconds(86400);

        $this->started_at = now();
        $finished_at = null;

        $stream = StreamWrapper::getResource($res->toPsrResponse()->getBody());
        foreach (Items::fromStream($stream) as $value) {
            if (! is_object($value) && ! is_array($value)) {
                continue;
            }
            if ($value->hasErrors && $value->quoteResponseError) {
                $error = $this->processErrors($value->quoteResponseError->requestInfo->supplier, $value->quoteResponseError->errorMessage);

                if (! in_array($error, $this->errors)) {
                    $this->errors[] = $error;
                }
            }

            if (empty($value->providerKey) || ! $value->quoteId || $value->priceMonthly == 0) {
                continue;
            }

            $temp = (array) $value;
            $supplierName = $this->getConsolidatedProviderName($temp['connectivitySupplierName']);
            $temp['providerKey'] = $temp['providerKey'] == 'virtual1' ? 'pxc' : $temp['providerKey'];
            $temp['providerName'] = $temp['providerName'] == 'Virtual1' ? 'PXC' : $temp['providerName'];
            $temp['connectivitySupplierName'] = $supplierName;
            $temp['connectivitySupplierGroup'] = $this->getConsolidatedProviderGroup($supplierName);
            $temp['speed'] = ceil($temp['speed'] / 100) * 100;
            $temp['code'] = '';
            foreach (array_keys($temp) as $itemKey) {
                if (! (is_string($temp[$itemKey]) || is_numeric($temp[$itemKey]))) {
                    continue;
                }
                $temp['code'] .= $temp[$itemKey];
            }

            $this->prices[] = [...$temp, 'code' => md5(Str::slug($temp['code']))];
            $this->tempFilters = FilterProvider::process($this->tempFilters, $temp);
            Product::write('live_prices', $this->file, $this->prices, false, $finished_at, $this->started_at, $this->tempFilters, $this->errors, $time);
        }
        $finished_at = now();
        Product::write('live_prices', $this->file, $this->prices, false, $finished_at, $this->started_at, $this->tempFilters, $this->errors, $time);
    }

    /**
     * Get the product file for error handling.
     *
     * @return mixed
     */
    protected function getProductFile()
    {
        return Product::where('key', 'live_prices')->where('filename', $this->file)->first();
    }

    /**
     * Write product data on failure.
     *
     * @param  mixed  $file
     */
    protected function writeProductOnFailure($file, array $errors): void
    {
        if ($file) {
            Product::write('live_prices', $this->file, $file['data'], false, now(), $file['created_at'], $file['filters'], $errors);
        }
    }
}

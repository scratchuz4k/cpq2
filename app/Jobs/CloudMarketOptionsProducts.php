<?php

namespace App\Jobs;

use App\Helpers\Api\CloudMarketAddress;
use App\Helpers\Api\CloudMarketApi;
use App\Models\Cloudmarket\Product;
use App\Models\Quote;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use JsonMachine\Items;

class CloudMarketOptionsProducts extends CloudMarket
{
    /**
     * Create a new job instance.
     */
    public function __construct(protected Quote $quote, protected string $file, protected CloudMarketAddress $address) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $res = CloudMarketApi::getOptions($this->quote, $this->address);
        $time = Carbon::now()->addSeconds(86400);

        $this->started_at = now();
        $this->updateCloudMarketSearchCache($this->file);
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
                    if (is_object($temp[$itemKey]) && get_class($temp[$itemKey]) === 'stdClass') {
                        continue;
                    }
                    if (is_array($temp[$itemKey])) {
                        continue;
                    }

                    $temp['code'] .= (string) $temp[$itemKey];
                }

                $temp['providerKey'] = $product[0]->data?->providerKey == 'pxc' ? 'virtual1' : $product[0]->data?->providerKey;
                $this->prices[] = [...$temp, 'code' => md5(Str::slug($temp['code']))];
                Product::write('options', $this->file, $this->prices, false, null, $this->started_at, $this->tempFilters, $this->errors, $time);
            }
        }

        Product::write('options', $this->file, $this->prices, false, now(), $this->started_at, $this->tempFilters, $this->errors, $time);
        $this->updateCloudMarketSearchCache($this->file);
    }

    /**
     * Get the product file for error handling.
     *
     * @return mixed
     */
    protected function getProductFile()
    {
        return Product::where('key', 'options')->where('filename', $this->file)->first();
    }

    /**
     * Write product data on failure.
     *
     * @param  mixed  $file
     */
    protected function writeProductOnFailure($file, array $errors): void
    {
        if ($file) {
            Product::write('options', $this->file, $file['data'], false, now(), $file['created_at'], $file['filters'], $errors);
        }
    }
}

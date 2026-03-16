<?php

namespace App\Jobs;

use App\Helpers\Api\CloudMarketApi;
use App\Models\Cloudmarket\Product;
use App\Models\Quote;
use Exception;
use stdClass;

class CloudMarketSaveQuote extends CloudMarket
{
    /**
     * Create a new job instance.
     */
    public function __construct(protected Quote $quote) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Keep original behavior for queued execution.
        $this->runSync();
    }

    public function runSync(): void
    {
        $this->quote->refresh();
        $res = CloudMarketApi::saveQuote($this->quote);
        $temp = $res->toPsrResponse()->getBody()->getContents();
        $res = json_decode($temp, true);

        if ($res == '') {
            throw new Exception('Something went wrong while saving your quote.');
        }

        if (array_key_exists('errors', $res)) {
            $data = new stdClass;
            $data->errors = $res['errors'];
            $this->quote->update(['data' => $data]);
            throw new Exception($res['errors']);
        }

        $data = (object) ($this->quote->data ?? []);

        $filename = $data->filename.$res['quoteReference'];
        Product::where('filename', $data->filename)->update(['filename' => $filename]);

        $product = self::getPrimaryProduct($this->quote);
        $data->quoteReference = $res['quoteReference'];
        $data->quoteId = $product->data?->quoteId;
        $this->quote->update(['data' => $data]);
    }

    private static function getPrimaryProduct(Quote $quote)
    {
        $products = collect($quote['items'] ?? $quote->items)->where(fn ($x) => in_array(data_get($x, 'data.type'), ['Primary Product'], true))->values();
        if (! count($products)) {
            throw new Exception('No Primary Product Available');
        }

        return $products[0];
    }
}

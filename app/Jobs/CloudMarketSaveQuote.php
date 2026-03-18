<?php

namespace App\Jobs;

use App\Helpers\Api\CloudMarketApi;
use App\Models\Cloudmarket\Product;
use App\Models\Quote;
use Exception;
use Illuminate\Support\Facades\Cache;

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
            throw new Exception('CloudMarketApi::saveQuote request: Something went wrong while saving your quote.');
        }

        if (array_key_exists('errors', $res)) {
            $data = new stdClass;
            $data->errors = $res['errors'];
            $this->quote->update(['data' => $data]);
            throw new Exception($res['errors']);
        }

        $quoteReference = $res['quoteReference'] ?? null;

        $data = $this->quote->data ?? [];
        $baseFilename = isset($data['filename']) && is_string($data['filename']) ? $data['filename'] : null;

        if ($quoteReference && $baseFilename) {
            if (! str_ends_with($baseFilename, $quoteReference)) {

                $newBaseFilename = $baseFilename.$quoteReference;

                $productsToRename = Product::where('filename', 'like', '%'.$baseFilename.'%')->get(['filename']);

                foreach ($productsToRename as $product) {
                    $oldFilename = (string) $product->filename;
                    $newFilename = $oldFilename.$quoteReference;

                    if (str_ends_with($oldFilename, $quoteReference) || $newFilename === $oldFilename) {
                        continue;
                    }

                    $cachedValue = Cache::get($oldFilename);
                    if (! is_null($cachedValue)) {
                        Cache::put($newFilename, $cachedValue, now()->addHours(24));
                    }
                    Cache::forget($oldFilename);

                    $product->update([
                        'filename' => $newFilename,
                    ]);
                }

                $data['filename'] = $newBaseFilename;
            }
        }

        $productPrimary = self::getPrimaryProduct($this->quote);
        $data['quoteReference'] = $quoteReference;
        $data['quoteId'] = $productPrimary->data?->quoteId;
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

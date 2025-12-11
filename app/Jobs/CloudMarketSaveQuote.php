<?php

namespace App\Jobs;

use App\Helpers\Api\CloudMarketApi;
use App\Models\Cloudmarket\Product;
use App\Models\Quote;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Jobs\TenantAware;
use stdClass;

class CloudMarketSaveQuote implements ShouldQueue, TenantAware
{
    use Queueable;

    public $timeout = 3600;

    public $prices = [];

    public $errors = [];

    public $started_at = null;

    public $tempFilters = [];

    /**
     * Create a new job instance.
     */
    public function __construct(protected Quote $quote) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->quote->refresh();
        $res = CloudMarketApi::saveQuote($this->quote);
        $temp = $res->toPsrResponse()->getBody()->getContents();
        $res = json_decode($temp, true);

        if ($res == '') {
            throw new Exception('Something went wrong!');
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

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) env('APP_ENV')).'] CloudMarketSaveQuote failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'CPQ',
            'response' => $exception->getMessage(),
        ];
        $errors = [];

        $errors[] = $error;
        array_merge($errors, $file?->errors ?? []);
        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) env('APP_ENV')).'] CloudMarketSaveQuote failed with error: '.json_encode($errors);

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

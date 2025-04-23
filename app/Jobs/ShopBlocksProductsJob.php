<?php

namespace App\Jobs;

use App\Enums\PricePlanEnum;
use App\Helpers\Api\ShopBlocksApi;
use App\Helpers\Files\ShopBlocksFile;
use App\Helpers\FilterProvider;
use App\Models\PricePlan;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Jobs\TenantAware;

class ShopBlocksProductsJob implements ShouldQueue, TenantAware
{
    use Queueable;

    public $timeout = 3600;

    public $products = [];

    public $errors = [];

    public $started_at = null;

    public $tempFilters = [
        'model' => [],
    ];

    /**
     * Create a new job instance.
     */
    public function __construct(protected ShopBlocksFile $file) {}

    public function middleware()
    {
        return [(new WithoutOverlapping('ShopBlocksProducts'))];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $filters = [
            'updated_after' => null,
        ];
        $this->started_at = now();
        $content = $this->file->getContent();

        foreach ($content['data'] as $temp) {
            $this->products[$temp['sku']] = $temp;
            $filters['updated_after'] = $temp['updated'] > $filters['updated_after'] ? $temp['updated'] : $filters['updated_after'];
        }

        $filters['updated_after'] = $content['finished_at'] == null ? null : $content['finished_at'];
        $page = 0;
        do {
            $page++;
            $res = (new ShopBlocksApi)->getArticles(500, $page, $filters);
            if (! array_key_exists('data', $res)) {
                break;
            }
            $last_page = array_key_exists('meta', $res) ? $res['meta']['last_page'] : 1;

            $pricePlanId = PricePlan::where('code', PricePlanEnum::SINGLE->name)->value('id');
            foreach ($res['data'] as $key => $product) {

                $this->products[$product['sku']] = $product;
                $this->products[$product['sku']]['code'] = $product['sku'];
                $this->products[$product['sku']]['model'] = (string) Str::of($product['name'])->words(3, '');
                $this->products[$product['sku']]['price_plan_id'] = $pricePlanId;
                $this->products[$product['sku']]['price'] = [
                    'code' => 1,
                    'tax_rate' => 20,
                    'net' => (float) $product['price'],
                    'tax' => (float) $product['price'] * 0.2,
                    'gross' => (float) $product['price'] * 1.2,
                ];
                $this->tempFilters = []; //FilterProvider::process($this->tempFilters, $products[$key], true);
            }

            $this->file->write(array_values($this->products), true, null, $this->started_at, $this->tempFilters, $this->errors);

        } while ($page != $last_page);

        $this->file->write(array_values($this->products), true, now(), $this->started_at, $this->tempFilters, $this->errors);
    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper(env('APP_ENV')).'] ShopBlocksProducts failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'CPQ',
            'response' => $exception->getMessage(),
        ];

        $file = $this->file->getContent();
        $file['errors'][] = $error;

        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper(env('APP_ENV')).'] ShopBlocksProducts failed with error: '.json_encode($file['errors']);

        $this->file->write($file['data'], false, now(), $file['created_at'], $file['filters'], $file['errors']);
        Log::error($message);
        if (env('DISCORD_CLOUDMARKET_JOBS')) {
            Http::post(env('DISCORD_CLOUDMARKET_JOBS'), ['content' => $message]);
        }
    }
}

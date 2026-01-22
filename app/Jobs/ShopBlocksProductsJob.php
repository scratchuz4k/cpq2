<?php

namespace App\Jobs;

use App\Helpers\Api\ShopBlocksApi;
use App\Models\ShopBlocks\Product;
use App\Models\ShopBlocks\ProductDimension;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Throwable;

class ShopBlocksProductsJob implements NotTenantAware, ShouldQueue
{
    use Queueable;

    public $timeout = 3600;

    public $products = [];

    public $errors = [];

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('shopblocks');
    }

    public function middleware()
    {
        return [(new WithoutOverlapping('sync:ShopBlocksProducts'))->expireAfter(180)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $page = 1;
        DB::connection('mysql_shopblocks')->statement('SET FOREIGN_KEY_CHECKS=0;');
        ProductDimension::truncate();
        Product::truncate();
        DB::connection('mysql_shopblocks')->statement('SET FOREIGN_KEY_CHECKS=1;');

        // Instead of getting products from the last updated time, we will get all products for now
        // $product = Product::latest('updated')->first();
        // filters = ['updatedAfter' => $product->updated];
        $filters = [];

        $time = Carbon::now()->addSeconds(86400);
        $res = (new ShopBlocksApi)->getArticles(200, $page, $filters);
        if (! array_key_exists('data', $res)) {
            return;
        }
        $last_page = array_key_exists('meta', $res) ? $res['meta']['last_page'] : 1;
        $total_products = array_key_exists('meta', $res) ? $res['meta']['total'] : (200 * $last_page);

        Log::info("[JOB] ShopBlocksProductsJob | $last_page pages of $total_products products to be synced.");

        $jobs = [];
        for ($page = 1; $page <= $last_page; $page++) {
            $jobs[] = new ShopBlocksProductsSyncJob($time, $page, $filters);
        }

        Bus::batch($jobs)
            ->onQueue('shopblocks')
            ->then(function (Batch $batch) use ($last_page, $total_products) {
                Log::info("[JOB] ShopBlocksProductsJob | Successfully synced {$last_page} pages of {$total_products} products.");
            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error('[JOB] ShopBlocksProductsJob | Failed batch', ['error' => $e->getMessage()]);
            })
            ->dispatch();
    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) config('app.env')).'] ShopBlocksProductsJob failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'ShopBlocks',
            'response' => $exception->getMessage(),
        ];

        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) config('app.env')).'] ShopBlocksProductsJob failed with error: '.json_encode($error);

        Log::error($message);
        if (config('services.discord.shopblocks.jobs')) {
            Http::post(config('services.discord.shopblocks.jobs'), ['content' => $message]);
        }
    }
}

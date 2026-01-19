<?php

namespace App\Jobs;

use App\Helpers\Api\ShopBlocksApi;
use Exception;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class ShopBlocksCategoriesJob implements NotTenantAware, ShouldQueue
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
        return [(new WithoutOverlapping('sync:ShopBlocksCategories'))->expireAfter(180)];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $page = 1;
        $res = (new ShopBlocksApi)->getCategories(200, $page);
        if (! array_key_exists('data', $res)) {
            return;
        }

        $last_page = array_key_exists('meta', $res) ? $res['meta']['last_page'] : 1;
        $total_categories = array_key_exists('meta', $res) ? $res['meta']['total'] : (200 * $last_page);

        Log::info("[JOB] ShopBlocksCategoriesJob | $last_page pages of $total_categories categories to be synced.");

        $jobs = [];
        for ($page = 1; $page <= $last_page; $page++) {
            $jobs[] = new ShopBlocksCategoriesSyncJob($page);
        }

        Bus::batch($jobs)
            ->onQueue('shopblocks')
            ->then(function (Batch $batch) use ($last_page, $total_categories) {
                Log::info("[JOB] ShopBlocksCategoriesJob | Successfully synced {$last_page} pages of {$total_categories} categories.");
            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error('[JOB] ShopBlocksCategoriesJob | Failed batch', ['error' => $e->getMessage()]);
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
        $message = '['.strtoupper((string) env('APP_ENV')).'] ShopBlocksCategoriesJob failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'ShopBlocks',
            'response' => $exception->getMessage(),
        ];

        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) env('APP_ENV')).'] ShopBlocksCategoriesJob failed with error: '.json_encode($error);

        Log::error($message);
        if (env('DISCORD_SHOPBLOCKS_JOBS')) {
            Http::post(env('DISCORD_SHOPBLOCKS_JOBS'), ['content' => $message]);
        }
    }
}

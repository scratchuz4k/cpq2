<?php

namespace App\Jobs;

use App\Helpers\Api\ShopBlocksApi;
use App\Models\ShopBlocks\Category;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
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
        $page = 0;
        $processedCount = 0;
        do {
            $page++;
            $res = (new ShopBlocksApi)->getCategories(200, $page);
            if (! array_key_exists('data', $res)) {
                break;
            }

            foreach ($res['data'] as $category) {

                $code = $category['id'];
                unset($category['id']);

                if (! is_null($category['parent_id'])) {
                    $temp = Category::where('code', $category['parent_id'])->first();

                    $category['parent_id'] = $temp?->id;
                }

                Category::updateOrCreate(['code' => $code], $category);
                $processedCount++;
            }

            $last_page = array_key_exists('meta', $res) ? $res['meta']['last_page'] : 1;
        } while ($page != $last_page);
        Log::info('[JOB] ShopBlocksCategoriesJob | '.$processedCount.' categories synced.');

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

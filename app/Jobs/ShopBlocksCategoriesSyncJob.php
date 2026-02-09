<?php

namespace App\Jobs;

use App\Helpers\Api\ShopBlocksApi;
use App\Models\ShopBlocks\Category;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class ShopBlocksCategoriesSyncJob implements NotTenantAware, ShouldQueue
{
    use Batchable, Queueable;

    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $page)
    {
        $this->onQueue('shopblocks');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $res = (new ShopBlocksApi)->getCategories(200, $this->page);
        if (! array_key_exists('data', $res)) {
            return;
        }

        foreach ($res['data'] as $category) {
            $code = $category['id'];
            unset($category['id']);

            if (! is_null($category['parent_id'])) {
                $temp = Category::where('code', $category['parent_id'])->first();
                $category['parent_id'] = $temp?->id;
            }

            Category::updateOrCreate(['code' => $code], $category);
        }
    }

    /**
     * The job failed to process.
     *
     * @return void
     */
    public function failed(Exception $exception)
    {
        $message = '['.strtoupper((string) env('APP_ENV')).'] ShopBlocksCategoriesSyncJob (page '.$this->page.') failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'ShopBlocks',
            'page' => $this->page,
            'response' => $exception->getMessage(),
        ];

        $message = '['.strtoupper((string) env('APP_ENV')).'] ShopBlocksCategoriesSyncJob failed with error: '.json_encode($error);

        Log::error($message);
        if (env('DISCORD_SHOPBLOCKS_JOBS')) {
            Http::post(env('DISCORD_SHOPBLOCKS_JOBS'), ['content' => $message]);
        }
    }
}

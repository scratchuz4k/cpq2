<?php

namespace App\Jobs;

use App\Helpers\Api\ShopBlocksApi;
use App\Models\ShopBlocks\Category;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class ShopBlocksCategoriesSyncJob implements NotTenantAware, ShouldQueue
{
    use Batchable, Queueable;

    public $timeout = 900;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $page)
    {
        $this->onQueue('shopblocks');
    }

    public function middleware(): array
    {
        return [
            new RateLimited('shopblocks-api'),
        ];
    }

    public function retryUntil(): \DateTime
    {
        return now()->addHours(2);
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

        $categoryCodes = collect($res['data'])
            ->flatMap(fn ($category) => [$category['id'], $category['parent_id']])
            ->filter()
            ->unique()
            ->values();
        $categories = Category::whereIn('code', $categoryCodes)->get()->keyBy('code');

        foreach ($res['data'] as $category) {
            $code = $category['id'];
            unset($category['id']);

            if (! is_null($category['parent_id'])) {
                $category['parent_id'] = $categories->get($category['parent_id'])?->id;
            }

            $categoryModel = $categories->get($code) ?? new Category(['code' => $code]);
            $categoryModel->fill($category);
            $categoryModel->save();
            $categories[$code] = $categoryModel;
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

<?php

namespace App\Jobs;

use App\Helpers\Api\ShopBlocksApi;
use App\Models\ShopBlocks\Category;
use Carbon\Carbon;
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
        $start = now();
        Log::info("ShopBlocksCategoriesJob | Started at $start");
        $page = 1;

        $time = Carbon::now()->addSeconds(86400);
        $page = 0;
        do {
            $page++;
            $res = (new ShopBlocksApi)->getCategories(200, $page);
            if (! array_key_exists('data', $res)) {
                break;
            }

            foreach ($res['data'] as $category) {

                $code = $category['id'];

                Category::updateOrCreate(['code' => $code], $category);
            }

            $last_page = array_key_exists('meta', $res) ? $res['meta']['last_page'] : 1;
        } while ($page != $last_page);

        $end = now();
        $date1 = Carbon::parse($start);
        $date2 = Carbon::parse($end);

        $diffInSeconds = $date2->diff($date1); // Output: 3600
        $time = $diffInSeconds->format('%y-%m-%d %h:%i:%s');
        Log::info("ShopBlocksCategoriesJob | Ended at $end | Elapsed Time: $time");
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
            'provider' => 'ShopBlocks',
            'response' => $exception->getMessage(),
        ];

        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper(env('APP_ENV')).'] ShopBlocksProducts failed with error: '.json_encode($error);

        Log::error($message);
        if (env('DISCORD_CLOUDMARKET_JOBS')) {
            Http::post(env('DISCORD_CLOUDMARKET_JOBS'), ['content' => $message]);
        }
    }
}

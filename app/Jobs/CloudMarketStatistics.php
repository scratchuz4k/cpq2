<?php

namespace App\Jobs;

use App\Helpers\Api\CloudMarketApi;
use App\Models\Cloudmarket\Statistic;
use Exception;
use GuzzleHttp\Psr7\StreamWrapper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonMachine\Items;
use Spatie\Multitenancy\Jobs\NotTenantAware;

class CloudMarketStatistics implements NotTenantAware, ShouldQueue
{
    use Queueable;

    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct() {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $createdSince = Carbon::now()->subDay(7)->startOfDay()->toIso8601String();
        $res = CloudMarketApi::getStatistics($createdSince);

        $stream = StreamWrapper::getResource($res->toPsrResponse()->getBody());
        foreach (Items::fromStream($stream) as $value) {
            if (! is_object($value) && ! is_array($value)) {
                continue;
            }

            $requestId = data_get($value, 'requestId');

            if (! $requestId) {
                continue;
            }

            Statistic::updateOrCreate(
                ['requestId' => $requestId],
                [
                    'requestId' => $requestId,
                    'requestDate' => data_get($value, 'requestDate', null),
                    'providers' => data_get($value, 'providers', []),
                ]
            );
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception)
    {
        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) env('APP_ENV')).'] CloudMarketStatistics failed with error: '.$exception->getMessage();

        $error = [
            'provider' => 'CPQ',
            'response' => $exception->getMessage(),
        ];

        // Handle the failure, log exception details, etc.
        $message = '['.strtoupper((string) env('APP_ENV')).'] CloudMarketStatistics failed with error: '.json_encode($error);

        Log::error($message);
        if (env('DISCORD_CLOUDMARKET_JOBS')) {
            Http::post(env('DISCORD_CLOUDMARKET_JOBS'), ['content' => $message]);
        }
    }
}

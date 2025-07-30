<?php

namespace App\Jobs;

use App\Enums\QuoteStatusesEnum;
use App\Models\Quote;
use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateStatusExpiredQuotesJob implements ShouldQueue
{
    use Queueable;

    private $statusThatCanBeUpdated = [
        QuoteStatusesEnum::QUOTE->name,
        QuoteStatusesEnum::PRICE->name,
        QuoteStatusesEnum::CONFIGURE->name,
    ];

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app()->instance('fromJob', true);
        $tenant = Tenant::current();
        $expiredStatus = QuoteStatusesEnum::EXPIRED->name;

        try {
            $quotesUpdated = 0;
            $expirationDays = ($tenant->configs && array_key_exists('quote_expiration_days', $tenant->configs))
                ? $tenant->configs['quote_expiration_days']
                : 14;
            $expirationDate = today()->subDays($expirationDays)->format('Y-m-d');

            Log::info("--------------------     {$tenant->name}     --------------------");
            Log::info("Expiring quotes created before {$expirationDate}");

            DB::transaction(function () use (
                &$quotesUpdated,
                $expiredStatus,
                $expirationDate
            ) {
                Quote::whereIn('status', $this->statusThatCanBeUpdated)
                    ->whereDate('created_at', '<=', $expirationDate)
                    ->chunkById(100, function ($quotes) use (&$quotesUpdated, $expiredStatus) {
                        foreach ($quotes as $quote) {
                            $originalStatus = $quote->status;
                            $quote->status = $expiredStatus;
                            $quote->save();

                            $quotesUpdated++;
                            Log::info("Quote ID {$quote->id} created at {$quote->created_at->format('Y-m-d')} updated from {$originalStatus} to {$expiredStatus}");
                        }
                    });
            });
        } catch (\Exception $e) {
            Log::error("Error processing tenant {$tenant->name}: {$e->getMessage()}");
        }

        Log::info("{$quotesUpdated} quotes update for {$tenant->name}");
        Log::info('-----------------------------------------------------------------');
    }
}

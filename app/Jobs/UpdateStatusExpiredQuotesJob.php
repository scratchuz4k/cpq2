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

            Log::info("UpdateStatusExpiredQuotesJob | ------------------- TENANT:  ({$tenant->id}) {$tenant->name} -------------------");
            Log::info("UpdateStatusExpiredQuotesJob | CONFIG: Expiring quotes created before {$expirationDate} ({$expirationDays} days ago})");
            Log::info('UpdateStatusExpiredQuotesJob | ---------------------------------------------------------------');

            DB::transaction(function () use (
                &$quotesUpdated,
                $expiredStatus,
                $expirationDate
            ) {
                $quotes = Quote::whereIn('status', $this->statusThatCanBeUpdated)
                    ->whereDate('created_at', '<=', $expirationDate)
                    ->get();

                foreach ($quotes as $quote) {
                    $originalStatus = $quote->status;
                    $quote->update([
                        'status' => $expiredStatus,
                    ]);

                    $quotesUpdated++;
                    Log::info("UpdateStatusExpiredQuotesJob | Quote ID {$quote->id} created at {$quote->created_at->format('Y-m-d')} updated from {$originalStatus} to {$expiredStatus}");
                }
            });
        } catch (\Exception $e) {
            Log::error("UpdateStatusExpiredQuotesJob | Error processing tenant ({$tenant->id}) {$tenant->name}: {$e->getMessage()}");
        }

        Log::info("UpdateStatusExpiredQuotesJob | {$quotesUpdated} quotes update for ({$tenant->id}) {$tenant->name}");
        Log::info('UpdateStatusExpiredQuotesJob | ---------------------------------------------------------------');
    }
}

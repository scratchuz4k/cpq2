<?php

namespace App\Jobs;

use App\Enums\NotificationEnum;
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

    private $statusThatCantBeUpdated = [
        QuoteStatusesEnum::SUBMITTED->name,
        QuoteStatusesEnum::COMPLETED->name,
        QuoteStatusesEnum::EXPIRED->name, ];

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
                ? (int) $tenant->configs['quote_expiration_days']
                : 30;
            $expirationDate = today()->subDays($expirationDays)->format('Y-m-d');

            $notificationDate = today()->subDays($expirationDays - 2)->format('Y-m-d');
            $notificationQuotes = Quote::whereNotIn('status', $this->statusThatCantBeUpdated)
                ->whereDate('created_at', '<=', $notificationDate)
                ->with(['account', 'user'])
                ->get();

            foreach ($notificationQuotes as $quote) {
                $description = 'Your quote #'.$quote->id.' for the account '.$quote->account->name.' is about to expire in 2 days.';
                createNotification(NotificationEnum::QuoteExpired, $quote->user, $description, route('quotes.show', ['quote' => $quote->id], false));

                Log::info("[JOB] UpdateStatusExpiredQuotesJob | Quote#{$quote->id} ({$quote->created_at->format('Y-m-d')}) notified to be expired");
            }

            DB::transaction(function () use (
                &$quotesUpdated,
                $expiredStatus,
                $expirationDate,
            ) {
                $quotes = Quote::whereNotIn('status', $this->statusThatCantBeUpdated)
                    ->whereDate('created_at', '<=', $expirationDate)
                    ->get();

                foreach ($quotes as $quote) {
                    $originalStatus = $quote->status;
                    $quote->update([
                        'status' => $expiredStatus,
                    ]);

                    $quotesUpdated++;
                    Log::info("[JOB] UpdateStatusExpiredQuotesJob | Quote#{$quote->id} ({$quote->created_at->format('Y-m-d')}) updated from {$originalStatus} to {$expiredStatus}");
                }
            });
        } catch (\Exception $e) {
            Log::error("[JOB] UpdateStatusExpiredQuotesJob | Error processing: {$e->getMessage()}");
        }

        Log::info("[JOB] UpdateStatusExpiredQuotesJob | {$quotesUpdated} quotes expired at {$expirationDate} ({$expirationDays} days ago)");
    }
}

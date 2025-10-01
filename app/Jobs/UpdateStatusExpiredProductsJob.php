<?php

namespace App\Jobs;

use App\Enums\ProductStatusesEnum;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateStatusExpiredProductsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        app()->instance('fromJob', true);
        $tenant = Tenant::current();

        try {
            $productsUpdated = 0;

            Log::info("UpdateStatusExpiredProductsJob | ------------------- TENANT:  ({$tenant->id}) {$tenant->name} -------------------");
            Log::info('UpdateStatusExpiredProductsJob | ---------------------------------------------------------------');

            $startOfCurrentDay = Carbon::now()->startOfDay();

            DB::transaction(function () use ($startOfCurrentDay, &$productsUpdated) {
                $products = Product::whereNot('status', [ProductStatusesEnum::RETIRED->name])
                    ->whereDate('expires_at', '<', $startOfCurrentDay)
                    ->whereNotNull('expires_at')
                    ->get();

                foreach ($products as $product) {
                    $product->update([
                        'status' => ProductStatusesEnum::RETIRED,
                        'is_active' => false,
                    ]);
                    $productsUpdated++;

                    Log::info("UpdateStatusExpiredProductsJob | Product ID {$product->id} was expired.");
                }
            });
        } catch (\Exception $e) {
            Log::error("UpdateStatusExpiredProductsJob | Error processing tenant ({$tenant->id}) {$tenant->name}: {$e->getMessage()}");
        }

        Log::info("UpdateStatusExpiredProductsJob | {$productsUpdated} products updated for ({$tenant->id}) {$tenant->name}");
        Log::info('UpdateStatusExpiredProductsJob | ---------------------------------------------------------------');
    }
}

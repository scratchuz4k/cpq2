<?php

use App\Models\PricePlan;
use App\Models\Quote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Recurring price plan discounts without a billing frequency unit are no longer
     * supported on the quote summary, so they become Monthly discounts. When the quote
     * already has a Monthly discount both are merged into one, keeping the same
     * sequential result: 100 - (100 - a) * (100 - b) / 100.
     */
    public function up(): void
    {
        $recurringPricePlanId = DB::table('price_plans')->where('code', 'MONTHLY')->value('id');

        if (! $recurringPricePlanId) {
            return;
        }

        $discounts = DB::table('quote_discounts')
            ->where('discountable_type', PricePlan::class)
            ->where('discountable_id', $recurringPricePlanId)
            ->whereNull('billing_frequency_unit')
            ->whereNull('deleted_at')
            ->get();

        foreach ($discounts as $discount) {
            $monthly = DB::table('quote_discounts')
                ->where('quote_id', $discount->quote_id)
                ->where('discountable_type', PricePlan::class)
                ->where('discountable_id', $recurringPricePlanId)
                ->where('billing_frequency_unit', 'MONTHS')
                ->whereNull('deleted_at')
                ->first();

            if ($monthly) {
                DB::table('quote_discounts')->where('id', $monthly->id)->update([
                    'discount' => 100 - (100 - $discount->discount) * (100 - $monthly->discount) / 100,
                    'updated_at' => now(),
                ]);
                DB::table('quote_discounts')->where('id', $discount->id)->update(['deleted_at' => now()]);
            } else {
                DB::table('quote_discounts')->where('id', $discount->id)->update([
                    'billing_frequency_unit' => 'MONTHS',
                    'updated_at' => now(),
                ]);
            }
        }

        Quote::withoutGlobalScopes()
            ->whereIn('id', $discounts->pluck('quote_id')->unique())
            ->each(function (Quote $quote) {
                $quote->computeTotals();
                $quote->saveQuietly();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Merged discounts cannot be split back into a recurring and a Monthly discount
    }
};

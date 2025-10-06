<?php

namespace Database\Seeders\Hotfix;

use App\Models\Quote;
use Illuminate\Database\Seeder;

class QuoteMarginRecalculate extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $quotes = Quote::get();
        foreach ($quotes as $quote) {
            $quote->tenant->makeCurrent();
            $quote->computeTotals();
            $quote->save();
            $quote->tenant->forgetCurrent();
        }
    }
}

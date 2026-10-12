<?php

use App\Enums\FrequencyUnitsEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Recurring price plan discounts with a billing frequency unit only apply to
     * the recurring items billed in that unit (Annual, Monthly, Daily). Without
     * a unit they keep applying to all recurring items.
     */
    public function up(): void
    {
        Schema::table('quote_discounts', function (Blueprint $table) {
            $table->enum('billing_frequency_unit', FrequencyUnitsEnum::names())->nullable()->after('discountable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('quote_discounts')->whereNotNull('billing_frequency_unit')->delete();

        Schema::table('quote_discounts', function (Blueprint $table) {
            $table->dropColumn('billing_frequency_unit');
        });
    }
};

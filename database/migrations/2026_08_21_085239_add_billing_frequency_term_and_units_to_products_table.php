<?php

use App\Enums\FrequencyUnitsEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('billing_frequency_value')->nullable()->after('price_plan_id');
            $table->enum('billing_frequency_unit', FrequencyUnitsEnum::names())->nullable()->after('billing_frequency_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('billing_frequency_value');
            $table->dropColumn('billing_frequency_unit');
        });
    }
};

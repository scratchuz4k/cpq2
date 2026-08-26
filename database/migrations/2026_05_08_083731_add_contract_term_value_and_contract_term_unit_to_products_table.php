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
            $table->integer('contract_term_value')->nullable()->after('price_plan_id');
            $table->enum('contract_term_unit', FrequencyUnitsEnum::names())->nullable()->after('contract_term_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('contract_term_value');
            $table->dropColumn('contract_term_unit');
        });
    }
};

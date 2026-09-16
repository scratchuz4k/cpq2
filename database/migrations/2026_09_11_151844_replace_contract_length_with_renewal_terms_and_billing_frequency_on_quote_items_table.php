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
     */
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->integer('renewal_terms_value')->nullable()->after('contract_length');
            $table->enum('renewal_terms_unit', FrequencyUnitsEnum::names())->nullable()->after('renewal_terms_value');
            $table->integer('billing_frequency_value')->nullable()->after('renewal_terms_unit');
            $table->enum('billing_frequency_unit', FrequencyUnitsEnum::names())->nullable()->after('billing_frequency_value');
        });

        DB::table('quote_items')->update([
            'renewal_terms_value' => DB::raw('contract_length'),
            'renewal_terms_unit' => FrequencyUnitsEnum::MONTHS->name,
            'billing_frequency_value' => 1,
            'billing_frequency_unit' => FrequencyUnitsEnum::MONTHS->name,
        ]);

        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn('contract_length');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            $table->integer('contract_length')->default(1)->after('billing_frequency_unit');
        });

        DB::table('quote_items')->update([
            'contract_length' => DB::raw('COALESCE(renewal_terms_value, 1)'),
        ]);

        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn('renewal_terms_value');
            $table->dropColumn('renewal_terms_unit');
            $table->dropColumn('billing_frequency_value');
            $table->dropColumn('billing_frequency_unit');
        });
    }
};

<?php

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
        Schema::table('prices', function (Blueprint $table) {
            $table->foreignId('price_plan_id')->after('product_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $table->foreignId('price_plan_id')->after('product_id')->nullable()->constrained()->nullOnDelete();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('price_plan_id')->after('product_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->dropForeign(['price_plan_id']);
            $table->dropColumn(['price_plan_id']);
        });
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropForeign(['price_plan_id']);
            $table->dropColumn(['price_plan_id']);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['price_plan_id']);
            $table->dropColumn(['price_plan_id']);
        });
    }
};

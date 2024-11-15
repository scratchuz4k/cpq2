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
        Schema::create('order_price_plans', function (Blueprint $table) {
            $table->id();
            $table->string('order_id');
            $table->string('price_plan_id');
            $table->decimal('discount', 12, 4)->default(0);
            $table->timestamps();
        });
        Schema::create('quote_price_plans', function (Blueprint $table) {
            $table->id();
            $table->string('quote_id');
            $table->string('price_plan_id');
            $table->decimal('discount', 12, 4)->default(0);
            $table->timestamps();
        });
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['discount']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_price_plans');
        Schema::dropIfExists('quote_price_plans');

        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('discount', 12, 4)->default(0);
        });
    }
};

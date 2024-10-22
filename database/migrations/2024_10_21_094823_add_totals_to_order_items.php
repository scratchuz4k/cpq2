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
        Schema::table('order_items', function (Blueprint $table) {
            $table->double('quantity')->default(0)->change();
            $table->double('original_price')->default(0);
            $table->double('original_price_monthly')->default(0);
            $table->double('price')->default(0);
            $table->double('price_monthly')->default(0);
            $table->double('margin')->default(0);
            $table->double('discount')->default(0);
            $table->double('total')->default(0);
            $table->double('total_tax')->default(0);
            $table->double('total_gross')->default(0);
            $table->double('total_monthly')->default(0);
            $table->double('total_monthly_tax')->default(0);
            $table->double('total_monthly_gross')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->change();
            $table->dropColumn([
                'total',
                'total_monthly',
                'margin',
                'discount',
                'price',
                'price_monthly',
            ]);
        });
    }
};

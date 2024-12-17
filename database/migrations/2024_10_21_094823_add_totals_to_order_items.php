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
            $table->decimal('quantity', 12, 4)->default(0)->change();
            $table->decimal('raw_price', 12, 4)->default(0);
            $table->decimal('raw_price_monthly', 12, 4)->default(0);
            $table->decimal('original_price', 12, 4)->default(0);
            $table->decimal('original_price_monthly', 12, 4)->default(0);
            $table->decimal('price', 12, 4)->default(0);
            $table->decimal('price_monthly', 12, 4)->default(0);
            $table->decimal('margin', 12, 4)->default(0);
            $table->decimal('discount', 12, 4)->default(0);
            $table->decimal('total', 12, 4)->default(0);
            $table->decimal('total_tax', 12, 4)->default(0);
            $table->decimal('total_gross', 12, 4)->default(0);
            $table->decimal('total_monthly', 12, 4)->default(0);
            $table->decimal('total_monthly_tax', 12, 4)->default(0);
            $table->decimal('total_monthly_gross', 12, 4)->default(0);
            $table->decimal('tax_order_item', 12, 4)->default(0);
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
                'original_price',
                'original_price_monthly',
                'price',
                'price_monthly',
                'margin',
                'discount',
                'total',
                'total_tax',
                'total_gross',
                'total_monthly',
                'total_monthly_tax',
                'total_monthly_gross',
                'tax_order_item',
            ]);
        });
    }
};

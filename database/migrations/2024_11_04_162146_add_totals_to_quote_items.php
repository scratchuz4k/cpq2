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
        Schema::table('quote_items', function (Blueprint $table) {
            $table->double('quantity')->default(0)->change();
            $table->double('raw_price')->default(0);
            $table->double('raw_price_monthly')->default(0);
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
            $table->double('tax_quote_item')->default(0);
            $table->string('product_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
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
                'tax_quote_item',
                'product_code',
            ]);
        });
    }
};

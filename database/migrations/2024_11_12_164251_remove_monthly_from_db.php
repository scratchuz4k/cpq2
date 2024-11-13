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
            $table->dropColumn([
                'raw_price_monthly',
                'original_price_monthly',
                'price_monthly',
                'total_monthly',
                'total_monthly_tax',
                'total_monthly_gross',
            ]);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'total_monthly',
                'total_monthly_tax',
                'total_monthly_gross',
            ]);
        });
        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn([
                'net_monthly',
                'tax_monthly',
                'gross_monthly',
            ]);
        });
        Schema::table('quote_items', function (Blueprint $table) {
            $table->dropColumn([
                'raw_price_monthly',
                'original_price_monthly',
                'price_monthly',
                'total_monthly',
                'total_monthly_tax',
                'total_monthly_gross',
            ]);
        });
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'total_monthly',
                'total_monthly_tax',
                'total_monthly_gross',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('raw_price_monthly', 12, 4)->default(0);
            $table->decimal('original_price_monthly', 12, 4)->default(0);
            $table->decimal('price_monthly', 12, 4)->default(0);
            $table->decimal('total_monthly', 12, 4)->default(0);
            $table->decimal('total_monthly_tax', 12, 4)->default(0);
            $table->decimal('total_monthly_gross', 12, 4)->default(0);
            //
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_monthly', 12, 4)->default(0);
            $table->decimal('total_monthly_tax', 12, 4)->default(0);
            $table->decimal('total_monthly_gross', 12, 4)->default(0);
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('net_monthly', 12, 2)->default(0)->after('gross');
            $table->decimal('tax_monthly', 12, 2)->default(0)->after('net_monthly');
            $table->decimal('gross_monthly', 12, 2)->default(0)->after('tax_monthly');
        });

        Schema::table('quote_items', function (Blueprint $table) {
            $table->decimal('raw_price_monthly', 12, 4)->default(0);
            $table->decimal('original_price_monthly', 12, 4)->default(0);
            $table->decimal('price_monthly', 12, 4)->default(0);
            $table->decimal('total_monthly', 12, 4)->default(0);
            $table->decimal('total_monthly_tax', 12, 4)->default(0);
            $table->decimal('total_monthly_gross', 12, 4)->default(0);
            //
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('total_monthly', 12, 4)->default(0);
            $table->decimal('total_monthly_tax', 12, 4)->default(0);
            $table->decimal('total_monthly_gross', 12, 4)->default(0);
        });
    }
};

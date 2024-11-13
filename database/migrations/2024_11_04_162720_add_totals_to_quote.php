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
        Schema::table('quotes', function (Blueprint $table) {
            $table->double('contract_length')->default(12);
            $table->double('margin')->default(0);
            $table->double('discount')->default(0);
            $table->double('total')->default(0);
            $table->double('total_tax')->default(0);
            $table->double('total_gross')->default(0);
            $table->double('total_monthly')->default(0);
            $table->double('total_monthly_tax')->default(0);
            $table->double('total_monthly_gross')->default(0);
            $table->double('commission_value')->default(0);
            $table->double('commission_rate')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'contract_length',
                'margin',
                'discount',
                'total',
                'total_tax',
                'total_gross',
                'total_monthly',
                'total_monthly_tax',
                'total_monthly_gross',
                'commission_value',
                'tax_commission',
            ]);
        });
    }
};
